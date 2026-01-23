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

        // Stats for Cards with amounts
        $stats = [
            'all' => ['count' => OutCheque::count(), 'amount' => OutCheque::sum('amount')],
            'sent' => ['count' => OutCheque::where('status', 'sent')->count(), 'amount' => OutCheque::where('status', 'sent')->sum('amount')],
            'realized' => ['count' => OutCheque::where('status', 'realized')->count(), 'amount' => OutCheque::where('status', 'realized')->sum('amount')],
            'returned' => ['count' => OutCheque::where('status', 'returned')->count(), 'amount' => OutCheque::where('status', 'returned')->sum('amount')],
        ];

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('payee_name', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%");
            });
        }

        // Payee Name filter (exact match for dropdown)
        if ($request->payee_name) {
            $query->where('payee_name', $request->payee_name);
        }

        // Bank filter
        if ($request->bank_id) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Single date filter (cheque_date)
        if ($request->cheque_date) {
            $query->whereDate('cheque_date', $request->cheque_date);
        }

        $cheques = $query->latest()->paginate(10)->withQueryString();
        $banks = Bank::all();
        $payees = OutCheque::select('payee_name')->distinct()->orderBy('payee_name')->pluck('payee_name');
        return view('cheque_operations.out_cheques.index', compact('cheques', 'stats', 'banks', 'payees'));
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
