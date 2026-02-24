<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ThirdPartyCheque;
use App\Models\InCheque;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ThirdPartyChequesExport;

class ThirdPartyChequeController extends Controller
{
    // ... constructor ...

    public function export(Request $request)
    {
        $query = ThirdPartyCheque::with('inCheque.bank');

        if ($request->search) {
            $query->where('third_party_name', 'like', "%{$request->search}%");
        }

        // Third Party Name filter
        if ($request->third_party_name) {
            $query->where('third_party_name', 'like', "%{$request->third_party_name}%");
        }

        // Bank filter (through inCheque relationship)
        if ($request->bank_id) {
            $query->whereHas('inCheque', function ($q) use ($request) {
                $q->where('bank_id', $request->bank_id);
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter (using transfer_date)
        if ($request->from_date) {
            $query->where('transfer_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('transfer_date', '<=', $request->to_date);
        }

        $cheques = $query->latest()->get();

        if ($request->has('export') && $request->export == 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('cheque_operations.third_party_cheques.pdf', compact('cheques'))
                ->download('third_party_cheques_export_' . now()->format('YmdHis') . '.pdf');
        }

        return Excel::download(new ThirdPartyChequesExport($cheques), 'third_party_cheques_export_' . now()->format('YmdHis') . '.xlsx');
    }

    // ... existing index ...
    public function __construct()
    {
        $this->middleware('permission:third-party-cheque-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:third-party-cheque-edit', ['only' => ['update']]);
        $this->middleware('permission:third-party-cheque-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $query = ThirdPartyCheque::with('inCheque.bank');

        // Stats for Cards with amounts
        $stats = [
            'all' => [
                'count' => ThirdPartyCheque::count(),
                'amount' => ThirdPartyCheque::with('inCheque')->get()->sum(fn($tp) => $tp->inCheque->amount ?? 0)
            ],
            'received' => [
                'count' => ThirdPartyCheque::where('status', 'received')->count(),
                'amount' => ThirdPartyCheque::where('status', 'received')->with('inCheque')->get()->sum(fn($tp) => $tp->inCheque->amount ?? 0)
            ],
            'realized' => [
                'count' => ThirdPartyCheque::where('status', 'realized')->count(),
                'amount' => ThirdPartyCheque::where('status', 'realized')->with('inCheque')->get()->sum(fn($tp) => $tp->inCheque->amount ?? 0)
            ],
            'returned' => [
                'count' => ThirdPartyCheque::where('status', 'returned')->count(),
                'amount' => ThirdPartyCheque::where('status', 'returned')->with('inCheque')->get()->sum(fn($tp) => $tp->inCheque->amount ?? 0)
            ],
        ];

        if ($request->search) {
            $query->where('third_party_name', 'like', "%{$request->search}%");
        }

        // Third Party Name filter
        if ($request->third_party_name) {
            $query->where('third_party_name', 'like', "%{$request->third_party_name}%");
        }

        // Bank filter (through inCheque relationship)
        if ($request->bank_id) {
            $query->whereHas('inCheque', function ($q) use ($request) {
                $q->where('bank_id', $request->bank_id);
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter (using transfer_date)
        if ($request->from_date) {
            $query->where('transfer_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('transfer_date', '<=', $request->to_date);
        }

        $perPage = $request->get('per_page', 10);
        if ($perPage === 'all') {
            $perPage = 1000000;
        }

        $cheques = $query->latest()->paginate($perPage)->withQueryString();
        $banks = \App\Models\Bank::all();
        return view('cheque_operations.third_party_cheques.index', compact('cheques', 'stats', 'banks'));
    }

    public function update(Request $request, ThirdPartyCheque $thirdPartyCheque)
    {
        $data = $request->validate([
            'status' => 'required|in:received,realized,returned',
            'notes' => 'nullable|string'
        ]);

        $thirdPartyCheque->update($data);

        // Update parent InCheque status based on 3rd party status
        if ($thirdPartyCheque->status == 'returned') {
            $thirdPartyCheque->inCheque->update(['status' => 'returned']);

            \App\Models\Cheque::create([
                'cheque_number' => $thirdPartyCheque->inCheque->cheque_number,
                'cheque_date' => $thirdPartyCheque->inCheque->cheque_date,
                'bank_id' => $thirdPartyCheque->inCheque->bank_id,
                'amount' => $thirdPartyCheque->inCheque->amount,
                'payer_name' => $thirdPartyCheque->inCheque->payer_name,
                'payment_status' => 'pending',
                'payee_name' => $thirdPartyCheque->third_party_name, // 3rd party who returned it
                'third_party_payment_status' => 'pending',
                'return_reason' => 'Returned from 3rd Party: ' . $thirdPartyCheque->third_party_name
            ]);
        } elseif ($thirdPartyCheque->status == 'realized') {
            $thirdPartyCheque->inCheque->update(['status' => 'realized']);
        }

        return back()->with('success', '3rd Party Cheque status updated');
    }

    public function destroy(ThirdPartyCheque $thirdPartyCheque)
    {
        $thirdPartyCheque->delete();
        return back()->with('success', '3rd Party Cheque record deleted');
    }
}
