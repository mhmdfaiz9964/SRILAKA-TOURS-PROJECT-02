<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OutCheque;
use App\Models\Bank;

class OutChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = OutCheque::with('bank');
        $stats = [
            'all' => ['count' => OutCheque::count(), 'amount' => OutCheque::sum('amount')],
            'sent' => ['count' => OutCheque::where('status', 'sent')->count(), 'amount' => OutCheque::where('status', 'sent')->sum('amount')],
            'realized' => ['count' => OutCheque::where('status', 'realized')->count(), 'amount' => OutCheque::where('status', 'realized')->sum('amount')],
            'bounced' => ['count' => OutCheque::where('status', 'bounced')->count(), 'amount' => OutCheque::where('status', 'bounced')->sum('amount')],
        ];
        if ($request->search) { $query->where(function($q) use ($request) { $q->where('payee_name', 'like', "%{$request->search}%")->orWhere('cheque_number', 'like', "%{$request->search}%"); }); }
        if ($request->payee_name) $query->where('payee_name', $request->payee_name);
        if ($request->bank_id) $query->where('bank_id', $request->bank_id);
        if ($request->status) $query->where('status', $request->status);
        if ($request->from_date) $query->where('cheque_date', '>=', $request->from_date);
        if ($request->to_date) $query->where('cheque_date', '<=', $request->to_date);
        $sort = $request->get('sort', 'latest');
        switch ($sort) { case 'oldest': $query->oldest(); break; case 'highest_amount': $query->orderByDesc('amount'); break; default: $query->latest(); }
        $cheques = $query->paginate($request->get('per_page', 15));
        return response()->json(['data' => $cheques->items(), 'meta' => ['current_page' => $cheques->currentPage(), 'last_page' => $cheques->lastPage(), 'total' => $cheques->total()], 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['cheque_date' => 'required|date', 'amount' => 'required|numeric', 'cheque_number' => 'required|digits:6', 'bank_id' => 'required|exists:banks,id', 'payee_name' => 'required|string', 'notes' => 'nullable|string', 'status' => 'required|in:sent,realized,bounced']);
        $cheque = OutCheque::create($data);
        return response()->json(['message' => 'Out Cheque added', 'cheque' => $cheque->load('bank')], 201);
    }

    public function show($id) { return response()->json(OutCheque::with('bank')->findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $cheque = OutCheque::findOrFail($id);
        $data = $request->validate(['cheque_date' => 'required|date', 'amount' => 'required|numeric', 'cheque_number' => 'required|digits:6', 'bank_id' => 'required|exists:banks,id', 'payee_name' => 'required|string', 'notes' => 'nullable|string', 'status' => 'required|in:sent,realized,bounced']);
        $cheque->update($data);
        return response()->json(['message' => 'Out Cheque updated', 'cheque' => $cheque->load('bank')]);
    }

    public function destroy($id) { OutCheque::findOrFail($id)->delete(); return response()->json(['message' => 'Out Cheque deleted']); }
}
