<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvestorsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class InvestorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:investor-list', ['only' => ['index', 'show', 'export']]);
        $this->middleware('permission:investor-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:investor-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:investor-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $query = Investor::query();

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('refund_date', [$request->start_date, $request->end_date]);
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
                    $query->orderByDesc('invest_amount');
                    break;
                case 'lowest_amount':
                    $query->orderBy('invest_amount');
                    break;
                case 'name_az':
                    $query->orderBy('name');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $investors = $query->paginate(10)->withQueryString();

        // Calculate Stats
        $stats = [
            'all' => [
                'count' => Investor::count(),
                'amount' => Investor::sum('invest_amount')
            ],
            'active' => [
                'count' => Investor::where('status', 'active')->count(),
                'amount' => Investor::where('status', 'active')->sum('invest_amount')
            ],
            'paid' => [
                'count' => Investor::where('status', 'paid')->count(),
                'amount' => Investor::where('status', 'paid')->sum('invest_amount')
            ],
            'waiting' => [
                'count' => Investor::where('status', 'waiting')->count(),
                'amount' => Investor::where('status', 'waiting')->sum('invest_amount')
            ],
            'total_paid_profit' => Investor::sum('paid_profit')
        ];
        
        return view('investors.index', compact('investors', 'stats'));
    }

    public function create()
    {
        return view('investors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,paid,pending,waiting',
            'invest_amount' => 'required|numeric|min:0',
            'expect_profit' => 'required|numeric|min:0',
            'paid_profit' => 'nullable|numeric|min:0',
            'collect_date' => 'nullable|date',
            'refund_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        Investor::create($data);

        return redirect()->route('investors.index')->with('success', 'Investor added successfully');
    }

    public function edit(Investor $investor)
    {
        return view('investors.edit', compact('investor'));
    }

    public function update(Request $request, Investor $investor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,paid,pending,waiting',
            'invest_amount' => 'required|numeric|min:0',
            'expect_profit' => 'required|numeric|min:0',
            'paid_profit' => 'nullable|numeric|min:0',
            'collect_date' => 'nullable|date',
            'refund_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        $investor->update($data);

        return redirect()->route('investors.index')->with('success', 'Investor updated successfully');
    }

    public function destroy(Investor $investor)
    {
        $investor->delete();
        return redirect()->route('investors.index')->with('success', 'Investor deleted successfully');
    }

    public function show(Investor $investor)
    {
        // Gather Ledger Data
        // 1. Initial Investment from specific record?
        // 2. Contributions to Purchases (PurchaseInvestor)
        // 3. Payouts (Payments where payable_id = investor->id and payable_type = Investor)

        $investments = \App\Models\PurchaseInvestor::where('investor_id', $investor->id)
            ->orWhere('investor_name', $investor->name)
            ->with('purchase')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->created_at,
                    'description' => "Investment in Purchase #{$item->purchase->invoice_number} ({$item->purchase->grn_number})",
                    'type' => 'investment',
                    'debit' => $item->amount,
                    'credit' => 0,
                    'ref' => $item->purchase->invoice_number
                ];
            });

        $payments = \App\Models\Payment::where('payable_id', $investor->id)
            ->where('payable_type', \App\Models\Investor::class)
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->payment_date,
                    'description' => "Payout: {$item->notes}",
                    'type' => 'payout',
                    'debit' => 0,
                    'credit' => $item->amount,
                    'ref' => $item->reference_number ?: '-'
                ];
            });

        // Add Initial Amount if it exists and not represented separately
        $ledger = collect($investments)->concat($payments)->sortBy('date');

        // Running Balance Calculation
        $balance = 0;
        $ledger = $ledger->map(function($item) use (&$balance) {
            $balance += ($item['debit'] - $item['credit']);
            $item['balance'] = $balance;
            return $item;
        });

        $totalInvested = $investments->sum('debit') + $investor->invest_amount; // Current invest_amount is often the total
        $totalReturned = $payments->sum('credit');

        return view('investors.show', compact('investor', 'ledger', 'totalInvested', 'totalReturned'));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $query = Investor::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $investors = $query->get();

        if ($format == 'pdf') {
            $pdf = Pdf::loadView('investors.pdf', compact('investors'));
            return $pdf->download('investors_report_' . now()->format('YmdHis') . '.pdf');
        }

        return Excel::download(new InvestorsExport($investors), 'investors_export_' . now()->format('YmdHis') . '.xlsx');
    }
}
