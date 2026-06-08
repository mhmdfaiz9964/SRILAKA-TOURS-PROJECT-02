<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $banks = Bank::all();
        return response()->json(['data' => $banks]);
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string', 'branch' => 'nullable|string', 'account_number' => 'nullable|string']);
        $bank = Bank::create($request->all());
        return response()->json(['message' => 'Bank created', 'bank' => $bank], 201);
    }
    public function show($id) { return response()->json(Bank::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $bank = Bank::findOrFail($id);
        $bank->update($request->all());
        return response()->json(['message' => 'Bank updated', 'bank' => $bank]);
    }
    public function destroy($id) { Bank::findOrFail($id)->delete(); return response()->json(['message' => 'Bank deleted']); }
    public function lookup() { return response()->json(Bank::select('id', 'name', 'branch')->get()); }
}
