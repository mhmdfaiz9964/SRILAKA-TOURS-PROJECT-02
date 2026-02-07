<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\OutCheque;
use App\Models\InCheque;
use App\Models\Bank;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OutChequesExport;
use Carbon\Carbon;

class OutChequeController extends Controller
{
    // ... constructor ...

    public function export(Request $request)
    {
        $query = OutCheque::with('bank');

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

        // Date range filter
        if ($request->from_date) {
            $query->whereDate('cheque_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('cheque_date', '<=', $request->to_date);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'latest': $query->latest(); break;
                case 'oldest': $query->oldest(); break;
                case 'highest_amount': $query->orderByDesc('amount'); break;
                case 'lowest_amount': $query->orderBy('amount'); break;
                case 'name_az': $query->orderBy('payee_name'); break;
                default: $query->latest();
            }
        } else {
             $query->latest();
        }

        $cheques = $query->get();

        if ($request->has('export') && $request->export == 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('cheque_operations.out_cheques.pdf', compact('cheques'))
                ->download('out_cheques_export_' . now()->format('YmdHis') . '.pdf');
        }

        return Excel::download(new OutChequesExport($cheques), 'out_cheques_export_' . now()->format('YmdHis') . '.xlsx');
    }

    // ... existing index ...
    public function __construct()
    {
        $this->middleware('permission:out-cheque-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:out-cheque-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:out-cheque-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:out-cheque-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $query = OutCheque::with('bank');

        // Stats for Cards with amounts
        $stats = [
            'all' => ['count' => OutCheque::count(), 'amount' => OutCheque::sum('amount')],
            'sent' => ['count' => OutCheque::where('status', 'sent')->count(), 'amount' => OutCheque::where('status', 'sent')->sum('amount')],
            'realized' => ['count' => OutCheque::where('status', 'realized')->count(), 'amount' => OutCheque::where('status', 'realized')->sum('amount')],
            'bounced' => ['count' => OutCheque::where('status', 'bounced')->count(), 'amount' => OutCheque::where('status', 'bounced')->sum('amount')],
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

        // Date range filter
        if ($request->from_date) {
            $query->whereDate('cheque_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('cheque_date', '<=', $request->to_date);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'latest':
                    $query->latest();
                    break;
                case 'oldest':
                    $query->oldest();
                    break;
                case 'highest_amount':
                    $query->orderByDesc('amount');
                    break;
                case 'lowest_amount':
                    $query->orderBy('amount');
                    break;
                case 'name_az':
                    $query->orderBy('payee_name');
                    break;
                default:
                    $query->latest();
            }
        } else {
             $query->latest();
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
            'status' => 'required|in:sent,realized,bounced'
        ]);

        $cheque = OutCheque::create($data);



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
            'status' => 'required|in:sent,realized,bounced'
        ]);

        $oldStatus = $outCheque->status;
        $outCheque->update($data);



        return redirect()->route('out-cheques.index')->with('success', 'Out Cheque updated successfully');
    }

    public function destroy(OutCheque $outCheque)
    {
        $outCheque->delete();
        return redirect()->route('out-cheques.index')->with('success', 'Out Cheque deleted successfully');
    }
}
