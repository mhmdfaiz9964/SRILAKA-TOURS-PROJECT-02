<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InCheque;
use App\Models\Bank;
use App\Models\ThirdPartyCheque;
use Carbon\Carbon;

class InChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = InCheque::with('bank');

        // Stats for Cards
        $stats = [
            'all' => InCheque::count(),
            'in_hand' => InCheque::where('status', 'received')->count(),
            'deposited' => InCheque::where('status', 'deposited')->count(),
            'transferred' => InCheque::where('status', 'transferred_to_third_party')->count(),
            'returned' => InCheque::where('status', 'returned')->count(),
            'realized' => InCheque::where('status', 'realized')->count(),
            'to_deposit_today' => InCheque::where('status', 'received')->whereDate('cheque_date', Carbon::today())->count(),
            'overdue' => InCheque::whereIn('status', ['received', 'deposited'])->whereDate('cheque_date', '<', Carbon::today())->count(),
        ];

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('payer_name', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $cheques = $query->latest()->paginate(10)->withQueryString();
        return view('cheque_operations.in_cheques.index', compact('cheques', 'stats'));
    }

    public function create()
    {
        $banks = Bank::all();
        return view('cheque_operations.in_cheques.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cheque_date' => 'required|date',
            'amount' => 'required|numeric',
            'cheque_number' => 'required|digits:6',
            'bank_id' => 'required|exists:banks,id',
            'payer_name' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:received,deposited,transferred_to_third_party,realized,returned',
            'third_party_name' => 'required_if:status,transferred_to_third_party'
        ]);

        $cheque = InCheque::create($data);

        if ($cheque->status == 'transferred_to_third_party') {
            ThirdPartyCheque::create([
                'in_cheque_id' => $cheque->id,
                'third_party_name' => $cheque->third_party_name,
                'transfer_date' => now(),
                'status' => 'received'
            ]);
        }

        if ($cheque->status == 'returned') {
            \App\Models\Cheque::create([
                'cheque_number' => $cheque->cheque_number,
                'cheque_date' => $cheque->cheque_date,
                'bank_id' => $cheque->bank_id,
                'amount' => $cheque->amount,
                'payer_name' => $cheque->payer_name,
                'payment_status' => 'pending',
                'return_reason' => 'Direct Entry / Returned'
            ]);
        }

        return redirect()->route('in-cheques.index')->with('success', 'In Cheque added successfully');
    }

    public function edit(InCheque $inCheque)
    {
        $banks = Bank::all();
        return view('cheque_operations.in_cheques.edit', compact('inCheque', 'banks'));
    }

    public function update(Request $request, InCheque $inCheque)
    {
        $data = $request->validate([
            'cheque_date' => 'required|date',
            'amount' => 'required|numeric',
            'cheque_number' => 'required|digits:6',
            'bank_id' => 'required|exists:banks,id',
            'payer_name' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:received,deposited,transferred_to_third_party,realized,returned',
            'third_party_name' => 'required_if:status,transferred_to_third_party'
        ]);

        $oldStatus = $inCheque->status;
        $inCheque->update($data);

        if ($inCheque->status == 'transferred_to_third_party' && $oldStatus != 'transferred_to_third_party') {
            ThirdPartyCheque::updateOrCreate(
                ['in_cheque_id' => $inCheque->id],
                [
                    'third_party_name' => $inCheque->third_party_name,
                    'transfer_date' => now(),
                    'status' => 'received'
                ]
            );
        }

        if ($inCheque->status == 'returned' && $oldStatus != 'returned') {
            \App\Models\Cheque::create([
                'cheque_number' => $inCheque->cheque_number,
                'cheque_date' => $inCheque->cheque_date,
                'bank_id' => $inCheque->bank_id,
                'amount' => $inCheque->amount,
                'payer_name' => $inCheque->payer_name,
                'payment_status' => 'pending',
                'return_reason' => 'Returned from Status Update'
            ]);
        }

        return redirect()->route('in-cheques.index')->with('success', 'In Cheque updated successfully');
    }

    public function destroy(InCheque $inCheque)
    {
        $inCheque->delete();
        return redirect()->route('in-cheques.index')->with('success', 'In Cheque deleted successfully');
    }
}
