<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InCheque;
use App\Models\Bank;
use App\Models\ThirdPartyCheque;
use App\Models\ThirdParty;
use Carbon\Carbon;

class InChequeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:in-cheque-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:in-cheque-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:in-cheque-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:in-cheque-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $query = InCheque::with('bank');

        // Stats for Cards with amounts
        $stats = [
            'all' => ['count' => InCheque::count(), 'amount' => InCheque::sum('amount')],
            'in_hand' => ['count' => InCheque::where('status', 'received')->count(), 'amount' => InCheque::where('status', 'received')->sum('amount')],
            'deposited' => ['count' => InCheque::where('status', 'deposited')->count(), 'amount' => InCheque::where('status', 'deposited')->sum('amount')],
            'transferred' => ['count' => InCheque::where('status', 'transferred_to_third_party')->count(), 'amount' => InCheque::where('status', 'transferred_to_third_party')->sum('amount')],
            'returned' => ['count' => InCheque::where('status', 'returned')->count(), 'amount' => InCheque::where('status', 'returned')->sum('amount')],
            'realized' => ['count' => InCheque::where('status', 'realized')->count(), 'amount' => InCheque::where('status', 'realized')->sum('amount')],
            'to_deposit_today' => ['count' => InCheque::where('status', 'received')->whereDate('cheque_date', Carbon::today())->count(), 'amount' => InCheque::where('status', 'received')->whereDate('cheque_date', Carbon::today())->sum('amount')],
            'overdue' => ['count' => InCheque::whereIn('status', ['received', 'deposited'])->whereDate('cheque_date', '<', Carbon::today())->count(), 'amount' => InCheque::whereIn('status', ['received', 'deposited'])->whereDate('cheque_date', '<', Carbon::today())->sum('amount')],
        ];

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('payer_name', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%");
            });
        }

        // Payer Name filter (exact match for dropdown)
        if ($request->payer_name) {
            $query->where('payer_name', $request->payer_name);
        }

        // Bank filter
        if ($request->bank_id) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->status) {
            if ($request->status == 'today') {
                // Filter for cheques to deposit today (received status + today's date)
                $query->where('status', 'received')->whereDate('cheque_date', Carbon::today());
            } elseif ($request->status == 'overdue') {
                // Filter for overdue cheques (received or deposited + past date)
                $query->whereIn('status', ['received', 'deposited'])->whereDate('cheque_date', '<', Carbon::today());
            } else {
                // Regular status filter
                $query->where('status', $request->status);
            }
        }

        // Date range filter
        if ($request->from_date) {
            $query->whereDate('cheque_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('cheque_date', '<=', $request->to_date);
        }

        $cheques = $query->latest()->paginate(10)->withQueryString();
        $banks = Bank::all();
        $payers = InCheque::select('payer_name')->distinct()->orderBy('payer_name')->pluck('payer_name');
        return view('cheque_operations.in_cheques.index', compact('cheques', 'stats', 'banks', 'payers'));
    }

    public function create()
    {
        $banks = Bank::all();
        $thirdParties = ThirdParty::all();
        return view('cheque_operations.in_cheques.create', compact('banks', 'thirdParties'));
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
        $thirdParties = ThirdParty::all();
        return view('cheque_operations.in_cheques.edit', compact('inCheque', 'banks', 'thirdParties'));
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
