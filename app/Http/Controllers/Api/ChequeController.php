<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cheque;
use App\Models\Payment;
use App\Models\Bank;
use App\Models\Reminder;
use App\Models\InCheque;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = Cheque::with('bank')->withSum('payments', 'amount')
            ->where('payment_status', '!=', 'paid')
            ->where(function($q) { $q->where('type', '!=', 'transferred_to_third_party')->orWhereNull('type'); });

        if ($request->bank_id) $query->where('bank_id', $request->bank_id);
        if ($request->payer_name) $query->where('payer_name', $request->payer_name);
        if ($request->search) { $query->where(function($q) use ($request) { $q->where('cheque_number', 'like', "%{$request->search}%")->orWhere('payer_name', 'like', "%{$request->search}%"); }); }
        if ($request->start_date && $request->end_date) $query->whereBetween('cheque_date', [$request->start_date, $request->end_date]);

        $total_balance = (clone $query)->get()->sum(fn($c) => $c->amount - ($c->payments_sum_amount ?? 0));
        $cheques = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json(['data' => $cheques->items(), 'meta' => ['current_page' => $cheques->currentPage(), 'last_page' => $cheques->lastPage(), 'total' => $cheques->total()], 'total_balance' => $total_balance]);
    }

    public function store(Request $request)
    {
        $request->validate(['cheque_number' => 'required|digits:6', 'cheque_date' => 'required|date', 'bank_id' => 'required|exists:banks,id', 'amount' => 'required|numeric', 'payer_name' => 'required']);
        $cheque = Cheque::create($request->all());
        return response()->json(['message' => 'Cheque created', 'cheque' => $cheque->load('bank')], 201);
    }

    public function show($id)
    {
        $cheque = Cheque::with('payments.bank', 'bank', 'reminders')->findOrFail($id);
        $totalPaid = $cheque->payments->sum('amount');
        return response()->json(['cheque' => $cheque, 'total_paid' => $totalPaid]);
    }

    public function update(Request $request, $id)
    {
        $cheque = Cheque::findOrFail($id);
        $request->validate(['cheque_number' => 'required|digits:6', 'cheque_date' => 'required|date', 'bank_id' => 'required|exists:banks,id', 'amount' => 'required|numeric', 'payer_name' => 'required']);
        $cheque->update($request->all());
        return response()->json(['message' => 'Cheque updated', 'cheque' => $cheque->load('bank')]);
    }

    public function destroy($id) { Cheque::findOrFail($id)->delete(); return response()->json(['message' => 'Cheque deleted']); }

    public function addPayment(Request $request, $id)
    {
        $cheque = Cheque::findOrFail($id);
        $request->validate(['amount' => 'required|numeric|min:0.01', 'payment_date' => 'required|date', 'payment_method' => 'required|in:bank_transfer,cash,cheque']);

        $data = $request->except(['document']);
        if ($request->payment_method == 'cheque') {
            $data['bank_id'] = $request->payment_cheque_bank_id;
            InCheque::create(['cheque_number' => $request->payment_cheque_number, 'cheque_date' => $request->payment_cheque_date, 'bank_id' => $request->payment_cheque_bank_id, 'amount' => $request->amount, 'payer_name' => $cheque->payer_name, 'status' => 'received', 'notes' => 'Payment for Returned Cheque #' . $cheque->cheque_number]);
        }
        $data['cheque_id'] = $cheque->id;
        Payment::create($data);

        $totalPaid = $cheque->payments()->sum('amount');
        if ($totalPaid >= $cheque->amount) $cheque->update(['payment_status' => 'paid']);
        elseif ($totalPaid > 0) $cheque->update(['payment_status' => 'partial paid']);

        return response()->json(['message' => 'Payment added', 'cheque' => $cheque->fresh()->load('bank')]);
    }

    public function storeReminder(Request $request, $id)
    {
        $cheque = Cheque::findOrFail($id);
        $request->validate(['reminder_date' => 'required|date', 'notes' => 'nullable|string']);
        Reminder::create(['cheque_id' => $cheque->id, 'payer_name' => $cheque->payer_name, 'reminder_date' => $request->reminder_date, 'notes' => $request->notes]);
        return response()->json(['message' => 'Reminder set']);
    }

    public function completeReminder($id) { Reminder::findOrFail($id)->update(['is_read' => true]); return response()->json(['message' => 'Reminder completed']); }

    public function paidCheques(Request $request)
    {
        $query = Cheque::with('bank')->withSum('payments', 'amount')->where('payment_status', 'paid');
        if ($request->search) { $query->where(function($q) use ($request) { $q->where('cheque_number', 'like', "%{$request->search}%")->orWhere('payer_name', 'like', "%{$request->search}%"); }); }
        $cheques = $query->latest()->paginate($request->get('per_page', 15));
        return response()->json(['data' => $cheques->items(), 'meta' => ['current_page' => $cheques->currentPage(), 'last_page' => $cheques->lastPage(), 'total' => $cheques->total()]]);
    }

    public function bulkUpdate(Request $request)
    {
        // Reuse the web controller's bulk update logic
        $controller = new \App\Http\Controllers\ChequeBulkController();
        return $controller->updateBulkStatus($request);
    }
}
