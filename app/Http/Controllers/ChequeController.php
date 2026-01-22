<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChequeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Cheque::with(['bank', 'reason']);

        if ($request->status) {
            $query->where('payment_status', $request->status);
        }
        if ($request->cheque_status) {
            $query->where('cheque_status', $request->cheque_status);
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('cheque_date', [$request->start_date, $request->end_date]);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('cheque_number', 'like', "%{$request->search}%")
                  ->orWhere('payer_name', 'like', "%{$request->search}%");
            });
        }

        $cheques = $query->latest()->paginate(10);
        return view('cheques.index', compact('cheques'));
    }

    public function create()
    {
        $banks = \App\Models\Bank::all();
        $reasons = \App\Models\ChequeReason::all();
        return view('cheques.create', compact('banks', 'reasons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cheque_number' => 'required',
            'cheque_date' => 'required|date',
            'bank_id' => 'required|exists:banks,id',
            'cheque_reason_id' => 'required|exists:cheque_reasons,id',
            'amount' => 'required|numeric',
            'payer_name' => 'required',
        ]);

        \App\Models\Cheque::create($request->all());

        return redirect()->route('cheques.index')->with('success', 'Cheque created successfully');
    }

    public function show(\App\Models\Cheque $cheque)
    {
        $cheque->load('payments');
        $totalPaid = $cheque->payments->sum('amount');
        return view('cheques.show', compact('cheque', 'totalPaid'));
    }

    public function edit(\App\Models\Cheque $cheque)
    {
        $banks = \App\Models\Bank::all();
        $reasons = \App\Models\ChequeReason::all();
        return view('cheques.edit', compact('cheque', 'banks', 'reasons'));
    }

    public function update(Request $request, \App\Models\Cheque $cheque)
    {
        $request->validate([
            'cheque_number' => 'required',
            'cheque_date' => 'required|date',
            'bank_id' => 'required|exists:banks,id',
            'cheque_reason_id' => 'required|exists:cheque_reasons,id',
            'amount' => 'required|numeric',
            'payer_name' => 'required',
            'payment_status' => 'required',
            'cheque_status' => 'required',
        ]);

        $cheque->update($request->all());

        return redirect()->route('cheques.index')->with('success', 'Cheque updated successfully');
    }

    public function destroy(\App\Models\Cheque $cheque)
    {
        $cheque->delete();
        return redirect()->route('cheques.index')->with('success', 'Cheque deleted successfully');
    }
}
