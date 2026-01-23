<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\OutCheque;
use App\Models\InCheque;
use App\Models\Bank;

class OutChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = OutCheque::with('bank');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('payee_name', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%");
            });
        }

        $cheques = $query->latest()->paginate(10)->withQueryString();
        return view('cheque_operations.out_cheques.index', compact('cheques'));
    }

    public function create()
    {
        $banks = Bank::all();
        return view('cheque_operations.out_cheques.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cheque_date' => 'required|date',
            'amount' => 'required|numeric',
            'cheque_number' => 'required|digits:6',
            'bank_id' => 'required|exists:banks,id',
            'payee_name' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:sent,realized,returned'
        ]);

        $cheque = OutCheque::create($data);

        if ($cheque->status == 'returned') {
            InCheque::create([
                'cheque_date' => $cheque->cheque_date,
                'amount' => $cheque->amount,
                'cheque_number' => $cheque->cheque_number,
                'bank_id' => $cheque->bank_id,
                'payer_name' => $cheque->payee_name, // In this case, the one who returned it
                'notes' => "Returned Out Cheque #" . $cheque->cheque_number,
                'status' => 'returned'
            ]);
        }

        return redirect()->route('out-cheques.index')->with('success', 'Out Cheque added successfully');
    }

    public function edit(OutCheque $outCheque)
    {
        $banks = Bank::all();
        return view('cheque_operations.out_cheques.edit', compact('outCheque', 'banks'));
    }

    public function update(Request $request, OutCheque $outCheque)
    {
        $data = $request->validate([
            'cheque_date' => 'required|date',
            'amount' => 'required|numeric',
            'cheque_number' => 'required|digits:6',
            'bank_id' => 'required|exists:banks,id',
            'payee_name' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:sent,realized,returned'
        ]);

        $oldStatus = $outCheque->status;
        $outCheque->update($data);

        if ($outCheque->status == 'returned' && $oldStatus != 'returned') {
            InCheque::create([
                'cheque_date' => $outCheque->cheque_date,
                'amount' => $outCheque->amount,
                'cheque_number' => $outCheque->cheque_number,
                'bank_id' => $outCheque->bank_id,
                'payer_name' => $outCheque->payee_name,
                'notes' => "Returned Out Cheque #" . $outCheque->cheque_number,
                'status' => 'returned'
            ]);
        }

        return redirect()->route('out-cheques.index')->with('success', 'Out Cheque updated successfully');
    }

    public function destroy(OutCheque $outCheque)
    {
        $outCheque->delete();
        return redirect()->route('out-cheques.index')->with('success', 'Out Cheque deleted successfully');
    }
}
