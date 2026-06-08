<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThirdPartyCheque;

class ThirdPartyChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = ThirdPartyCheque::with('inCheque.bank');
        if ($request->search) $query->where('third_party_name', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);
        $cheques = $query->latest()->paginate($request->get('per_page', 15));
        return response()->json(['data' => $cheques->items(), 'meta' => ['current_page' => $cheques->currentPage(), 'last_page' => $cheques->lastPage(), 'total' => $cheques->total()]]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['in_cheque_id' => 'required|exists:in_cheques,id', 'third_party_name' => 'required|string', 'transfer_date' => 'required|date', 'status' => 'required|in:received,realized,returned', 'notes' => 'nullable|string']);
        $cheque = ThirdPartyCheque::create($data);
        return response()->json(['message' => '3rd Party Cheque added', 'cheque' => $cheque->load('inCheque')], 201);
    }

    public function show($id) { return response()->json(ThirdPartyCheque::with('inCheque.bank')->findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $cheque = ThirdPartyCheque::findOrFail($id);
        $data = $request->validate(['third_party_name' => 'required|string', 'status' => 'required|in:received,realized,returned', 'notes' => 'nullable|string']);
        $cheque->update($data);
        return response()->json(['message' => '3rd Party Cheque updated', 'cheque' => $cheque->load('inCheque')]);
    }

    public function destroy($id) { ThirdPartyCheque::findOrFail($id)->delete(); return response()->json(['message' => '3rd Party Cheque deleted']); }
}
