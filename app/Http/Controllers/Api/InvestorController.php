<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Investor;
use App\Models\PurchaseInvestor;
use App\Models\Payment;

class InvestorController extends Controller
{
    public function index(Request $request)
    {
        $query = Investor::query();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        if ($request->start_date && $request->end_date) $query->whereBetween('refund_date', [$request->start_date, $request->end_date]);

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest': $query->oldest(); break;
            case 'highest_amount': $query->orderByDesc('invest_amount'); break;
            case 'lowest_amount': $query->orderBy('invest_amount'); break;
            case 'name_az': $query->orderBy('name'); break;
            default: $query->latest();
        }

        $investors = $query->paginate($request->get('per_page', 15));

        $stats = [
            'all' => ['count' => Investor::count(), 'amount' => Investor::sum('invest_amount')],
            'active' => ['count' => Investor::where('status', 'active')->count(), 'amount' => Investor::where('status', 'active')->sum('invest_amount')],
            'paid' => ['count' => Investor::where('status', 'paid')->count(), 'amount' => Investor::where('status', 'paid')->sum('invest_amount')],
            'waiting' => ['count' => Investor::where('status', 'waiting')->count(), 'amount' => Investor::where('status', 'waiting')->sum('invest_amount')],
            'total_paid_profit' => Investor::sum('paid_profit'),
        ];

        return response()->json(['data' => $investors->items(), 'meta' => ['current_page' => $investors->currentPage(), 'last_page' => $investors->lastPage(), 'total' => $investors->total()], 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'status' => 'required|in:active,paid,pending,waiting', 'invest_amount' => 'required|numeric|min:0', 'expect_profit' => 'required|numeric|min:0', 'paid_profit' => 'nullable|numeric', 'collect_date' => 'nullable|date', 'refund_date' => 'nullable|date', 'notes' => 'nullable|string']);
        $investor = Investor::create($data);
        return response()->json(['message' => 'Investor added', 'investor' => $investor], 201);
    }

    public function show($id)
    {
        $investor = Investor::findOrFail($id);
        $investments = PurchaseInvestor::where('investor_id', $investor->id)->orWhere('investor_name', $investor->name)->with('purchase')->get()
            ->map(fn($item) => ['date' => $item->created_at, 'description' => "Investment in Purchase #{$item->purchase->invoice_number}", 'type' => 'investment', 'debit' => $item->amount, 'credit' => 0]);
        $payments = Payment::where('payable_id', $investor->id)->where('payable_type', Investor::class)->get()
            ->map(fn($item) => ['date' => $item->payment_date, 'description' => "Payout: {$item->notes}", 'type' => 'payout', 'debit' => 0, 'credit' => $item->amount]);
        $ledger = collect($investments)->concat($payments)->sortBy('date')->values();
        $balance = 0;
        $ledger = $ledger->map(function($item) use (&$balance) { $balance += ($item['debit'] - $item['credit']); $item['balance'] = $balance; return $item; });

        return response()->json(['investor' => $investor, 'ledger' => $ledger, 'total_invested' => $investments->sum('debit') + $investor->invest_amount, 'total_returned' => $payments->sum('credit')]);
    }

    public function update(Request $request, $id)
    {
        $investor = Investor::findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:255', 'status' => 'required|in:active,paid,pending,waiting', 'invest_amount' => 'required|numeric|min:0', 'expect_profit' => 'required|numeric|min:0', 'paid_profit' => 'nullable|numeric', 'collect_date' => 'nullable|date', 'refund_date' => 'nullable|date', 'notes' => 'nullable|string']);
        $investor->update($data);
        return response()->json(['message' => 'Investor updated', 'investor' => $investor]);
    }

    public function destroy($id) { Investor::findOrFail($id)->delete(); return response()->json(['message' => 'Investor deleted']); }
}
