<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InCheque;
use App\Models\Bank;
use App\Models\ThirdPartyCheque;
use App\Models\Cheque;
use Carbon\Carbon;

class InChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = InCheque::with('bank');
        if (!$request->has('status') && !$request->has('search')) {
            $query->where('status', '!=', 'transferred_to_third_party');
        }

        $stats = [
            'all' => ['count' => InCheque::count(), 'amount' => InCheque::sum('amount')],
            'in_hand' => ['count' => InCheque::where('status', 'received')->count(), 'amount' => InCheque::where('status', 'received')->sum('amount')],
            'deposited' => ['count' => InCheque::where('status', 'deposited')->count(), 'amount' => InCheque::where('status', 'deposited')->sum('amount')],
            'transferred' => ['count' => InCheque::where('status', 'transferred_to_third_party')->count(), 'amount' => InCheque::where('status', 'transferred_to_third_party')->sum('amount')],
            'returned' => ['count' => InCheque::where('status', 'returned')->count(), 'amount' => InCheque::where('status', 'returned')->sum('amount')],
            'realized' => ['count' => InCheque::where('status', 'realized')->count(), 'amount' => InCheque::where('status', 'realized')->sum('amount')],
            'overdue' => ['count' => InCheque::whereIn('status', ['received', 'deposited'])->where('cheque_date', '<', Carbon::today()->toDateString())->count(), 'amount' => InCheque::whereIn('status', ['received', 'deposited'])->where('cheque_date', '<', Carbon::today()->toDateString())->sum('amount')],
        ];

        if ($request->search) { $query->where(function($q) use ($request) { $q->where('payer_name', 'like', "%{$request->search}%")->orWhere('cheque_number', 'like', "%{$request->search}%"); }); }
        if ($request->payer_name) $query->where('payer_name', $request->payer_name);
        if ($request->bank_id) $query->where('bank_id', $request->bank_id);
        if ($request->status) {
            if ($request->status == 'today') { $query->where('status', 'received')->whereDate('cheque_date', Carbon::today()); }
            elseif ($request->status == 'overdue') { $query->whereIn('status', ['received', 'deposited'])->whereDate('cheque_date', '<', Carbon::today()); }
            else { $query->where('status', $request->status); }
        }
        if ($request->from_date) $query->whereDate('cheque_date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('cheque_date', '<=', $request->to_date);

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest': $query->oldest(); break;
            case 'highest_amount': $query->orderByDesc('amount'); break;
            case 'lowest_amount': $query->orderBy('amount'); break;
            case 'name_az': $query->orderBy('payer_name'); break;
            default: $query->latest();
        }

        $cheques = $query->paginate($request->get('per_page', 15));
        return response()->json(['data' => $cheques->items(), 'meta' => ['current_page' => $cheques->currentPage(), 'last_page' => $cheques->lastPage(), 'total' => $cheques->total()], 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cheque_date' => 'required|date', 'amount' => 'required|numeric', 'cheque_number' => 'required|digits:6',
            'bank_id' => 'required|exists:banks,id', 'payer_name' => 'required|string', 'notes' => 'nullable|string',
            'status' => 'required|in:received,deposited,transferred_to_third_party,realized,returned',
            'third_party_name' => 'required_if:status,transferred_to_third_party'
        ]);
        $cheque = InCheque::create($data);
        if ($cheque->status == 'transferred_to_third_party') {
            ThirdPartyCheque::create(['in_cheque_id' => $cheque->id, 'third_party_name' => $cheque->third_party_name, 'transfer_date' => now(), 'status' => 'received']);
        }
        if ($cheque->status == 'returned') {
            Cheque::create(['cheque_number' => $cheque->cheque_number, 'cheque_date' => $cheque->cheque_date, 'bank_id' => $cheque->bank_id, 'amount' => $cheque->amount, 'payer_name' => $cheque->payer_name, 'payment_status' => 'pending', 'type' => 'returned', 'return_reason' => 'Direct Entry / Returned']);
        }
        return response()->json(['message' => 'In Cheque added', 'cheque' => $cheque->load('bank')], 201);
    }

    public function show($id) { return response()->json(InCheque::with('bank')->findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $in_cheque = InCheque::findOrFail($id);
        $data = $request->validate([
            'cheque_date' => 'required|date', 'amount' => 'required|numeric', 'cheque_number' => 'required|digits:6',
            'bank_id' => 'required|exists:banks,id', 'payer_name' => 'required|string', 'notes' => 'nullable|string',
            'status' => 'required|in:received,deposited,transferred_to_third_party,realized,returned',
            'third_party_name' => 'required_if:status,transferred_to_third_party'
        ]);
        $oldStatus = $in_cheque->status;
        $in_cheque->update($data);
        if ($in_cheque->status == 'transferred_to_third_party' && $oldStatus != 'transferred_to_third_party') {
            ThirdPartyCheque::updateOrCreate(['in_cheque_id' => $in_cheque->id], ['third_party_name' => $in_cheque->third_party_name, 'transfer_date' => now(), 'status' => 'received']);
        }
        if ($in_cheque->status == 'returned' && $oldStatus != 'returned') {
            Cheque::create(['cheque_number' => $in_cheque->cheque_number, 'cheque_date' => $in_cheque->cheque_date, 'bank_id' => $in_cheque->bank_id, 'amount' => $in_cheque->amount, 'payer_name' => $in_cheque->payer_name, 'payment_status' => 'pending', 'type' => 'returned', 'return_reason' => 'Returned from Status Update']);
        }
        return response()->json(['message' => 'In Cheque updated', 'cheque' => $in_cheque->load('bank')]);
    }

    public function destroy($id) { InCheque::findOrFail($id)->delete(); return response()->json(['message' => 'In Cheque deleted']); }
}
