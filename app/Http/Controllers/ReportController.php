<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Product;
use App\Models\InCheque;
use App\Models\OutCheque;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Investor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dailyLedger(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : now();
        $dateStr = $date->format('Y-m-d');

        // Define Default Heads
        $defaultHeads = [
            'income' => ['Tour Advance', 'Tour Final Payment', 'Other Income'],
            'expense' => ['Fuel', 'Driver Bata', 'Highway Ticket', 'Parking', 'Office Expenses', 'Salaries', 'Other Expenses']
        ];

        // Ensure default entries exist for the date
        foreach ($defaultHeads['income'] as $desc) {
            \App\Models\DailyLedgerEntry::firstOrCreate(
                ['date' => $dateStr, 'description' => $desc, 'type' => 'income'],
                ['amount' => 0]
            );
        }
        foreach ($defaultHeads['expense'] as $desc) {
            \App\Models\DailyLedgerEntry::firstOrCreate(
                ['date' => $dateStr, 'description' => $desc, 'type' => 'expense'],
                ['amount' => 0]
            );
        }

        // Fetch entries (including the newly created defaults)
        $entries = \App\Models\DailyLedgerEntry::whereDate('date', $dateStr)->get();

        $incomeEntries = $entries->where('type', 'income');
        $expenseEntries = $entries->where('type', 'expense');

        $totalIncome = $incomeEntries->sum('amount');
        $totalExpenses = $expenseEntries->sum('amount');

        // Calculate Opening Balance
        $pastEntries = \App\Models\DailyLedgerEntry::whereDate('date', '<', $dateStr)->get();
        $pastIncome = $pastEntries->where('type', 'income')->sum('amount');
        $pastExpenses = $pastEntries->where('type', 'expense')->sum('amount');
        
        $openingBalance = $pastIncome - $pastExpenses;
        $closingBalance = $openingBalance + $totalIncome - $totalExpenses;

        if ($request->has('export')) {
            if ($request->export == 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DailyLedgerExport($entries), 'daily_ledger_' . $dateStr . '.xlsx');
            } elseif ($request->export == 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily_ledger_pdf', compact(
                    'date', 'entries', 'totalIncome', 'totalExpenses', 'closingBalance'
                ));
                return $pdf->download('daily_ledger_' . $dateStr . '.pdf');
            }
        }

        return view('reports.daily_ledger', compact(
            'date',
            'incomeEntries',
            'expenseEntries',
            'totalIncome',
            'totalExpenses',
            'openingBalance',
            'closingBalance'
        ));
    }

    public function updateDailyLedger(Request $request)
    {
        $request->validate([
            'entries' => 'nullable|array',
            'entries.*.id' => 'required|exists:daily_ledger_entries,id',
            'entries.*.amount' => 'required|numeric',
            'new_entries' => 'nullable|array',
            'new_entries.*.description' => 'required|string',
            'new_entries.*.amount' => 'required|numeric',
            'new_entries.*.type' => 'required|in:income,expense',
        ]);

        if ($request->has('entries')) {
            foreach ($request->entries as $entryData) {
                \App\Models\DailyLedgerEntry::where('id', $entryData['id'])->update(['amount' => $entryData['amount']]);
            }
        }

        if ($request->has('new_entries')) {
            foreach ($request->new_entries as $newEntry) {
                if ($newEntry['description'] && $newEntry['amount'] !== null) {
                    \App\Models\DailyLedgerEntry::create([
                        'date' => $request->date,
                        'description' => $newEntry['description'],
                        'amount' => $newEntry['amount'],
                        'type' => $newEntry['type'],
                    ]);
                }
            }
        }

        return redirect()->route('reports.daily-ledger', ['date' => $request->date])->with('success', 'Ledger updated successfully');
    }

    public function balanceSheet(Request $request) 
    {
        $date = $request->date ? Carbon::parse($request->date) : now();
        $dateStr = $date->format('Y-m-d');
        
        // Define Default Heads matches the user's image
        $defaultHeads = [
            'asset' => [
                'Customer Outstanding', 
                'Cheque in Hand', 
                'RTN Cheque', 
                'Stock in Cost'
            ],
            'liability' => [
                'Supplier Out', 
                'Investors'
            ],
            'equity' => [
                'Profit/Lost'
            ]
        ];

        // Ensure default entries exist
        foreach ($defaultHeads as $type => $heads) {
            foreach ($heads as $head) {
                \App\Models\BalanceSheetEntry::firstOrCreate(
                    ['date' => $dateStr, 'name' => $head, 'category' => $type],
                    ['amount' => 0]
                );
            }
        }

        $entries = \App\Models\BalanceSheetEntry::whereDate('date', $dateStr)->get();

        $assets = $entries->where('category', 'asset');
        $liabilities = $entries->where('category', 'liability');
        $equity = $entries->where('category', 'equity');

        $totalAssets = $assets->sum('amount');
        $totalLiabilities = $liabilities->sum('amount');
        $totalEquity = $equity->sum('amount');

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        if ($request->has('export')) {
            if ($request->export == 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BalanceSheetExport($entries), 'balance_sheet_' . $dateStr . '.xlsx');
            } elseif ($request->export == 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.balance_sheet_pdf', compact(
                    'date', 'assets', 'liabilities', 'equity', 'totalAssets', 
                    'totalLiabilities', 'totalEquity', 'totalLiabilitiesAndEquity'
                ));
                return $pdf->download('balance_sheet_' . $dateStr . '.pdf');
            }
        }

        return view('reports.balance_sheet', compact(
            'date',
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'totalLiabilitiesAndEquity'
        ));
    }

    public function updateBalanceSheet(Request $request)
    {
        $request->validate([
            'entries' => 'nullable|array',
            'entries.*.id' => 'required|exists:balance_sheet_entries,id',
            'entries.*.amount' => 'required|numeric',
            'new_entries' => 'nullable|array',
            'new_entries.*.name' => 'required|string',
            'new_entries.*.amount' => 'required|numeric',
            'new_entries.*.category' => 'required|in:asset,liability,equity',
        ]);

        if ($request->has('entries')) {
            foreach ($request->entries as $entryData) {
                \App\Models\BalanceSheetEntry::where('id', $entryData['id'])->update(['amount' => $entryData['amount']]);
            }
        }

        if ($request->has('new_entries')) {
            foreach ($request->new_entries as $newEntry) {
                if ($newEntry['name'] && $newEntry['amount'] !== null) {
                    \App\Models\BalanceSheetEntry::create([
                        'date' => $request->date,
                        'name' => $newEntry['name'],
                        'amount' => $newEntry['amount'],
                        'category' => $newEntry['category'],
                    ]);
                }
            }
        }

        return redirect()->route('reports.balance-sheet', ['date' => $request->date])->with('success', 'Balance Sheet updated successfully');
    }
}
