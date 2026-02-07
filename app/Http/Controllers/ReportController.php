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

        // Check for Export Request (Works for both single day entry view)
        if ($request->has('export')) {
             $entries = \App\Models\DailyLedgerEntry::whereDate('date', $dateStr)->get();
             $totalIncome = $entries->where('type', 'income')->sum('amount');
             $totalExpenses = $entries->where('type', 'expense')->sum('amount');
             
             // Calculate Opening/Closing for PDF only if needed
             $pastEntries = \App\Models\DailyLedgerEntry::whereDate('date', '<', $dateStr)->get();
             $pastIncome = $pastEntries->where('type', 'income')->sum('amount');
             $pastExpenses = $pastEntries->where('type', 'expense')->sum('amount');
             $openingBalance = $pastIncome - $pastExpenses;
             $closingBalance = $openingBalance + $totalIncome - $totalExpenses;

            if ($request->export == 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DailyLedgerExport($entries), 'daily_ledger_' . $dateStr . '.xlsx');
            } elseif ($request->export == 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily_ledger_pdf', compact(
                    'date', 'entries', 'totalIncome', 'totalExpenses', 'closingBalance'
                ));
                return $pdf->download('daily_ledger_' . $dateStr . '.pdf');
            }
        }

        // --- ENTRY MODE (Always) ---
        
        // Define Default Heads
        $defaultHeads = [
            'income' => ['A/c Sales', 'Cash Sales', 'Old payment'],
            'expense' => ['Transport', 'Food', 'Bank Deposit', 'Other']
        ];

        // Cleanup Obsolete Heads (if amount is 0) to ensure view matches new layout
        $obsoleteHeads = [
            'Tour Advance', 'Tour Final Payment', 'Other Income', // Old Income
            'Fuel', 'Driver Bata', 'Highway Ticket', 'Parking', 'Office Expenses', 'Salaries', 'Other Expenses' // Old Expense
        ];
        
        \App\Models\DailyLedgerEntry::where('date', $dateStr)
            ->whereIn('description', $obsoleteHeads)
            ->where('amount', 0)
            ->delete();

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

        // Fetch entries
        $entries = \App\Models\DailyLedgerEntry::whereDate('date', $dateStr)->get();

        $incomeEntries = $entries->where('type', 'income');
        $expenseEntries = $entries->where('type', 'expense')->where('description', '!=', 'Salary');

        // Calculate totals EXCLUDING A/C Sales from income
        $totalIncome = $incomeEntries->where('description', '!=', 'A/c Sales')->sum('amount');
        $totalExpenses = $expenseEntries->sum('amount');

        // Fetch salary entries
        $salaryEntries = \App\Models\DailySalaryEntry::whereDate('date', $dateStr)->get();
        $totalSalary = $salaryEntries->sum('amount');

        // Opening/Closing Balance Calculations (EXCLUDING A/C Sales)
        $pastEntries = \App\Models\DailyLedgerEntry::whereDate('date', '<', $dateStr)->get();
        $pastIncome = $pastEntries->where('type', 'income')->where('description', '!=', 'A/c Sales')->sum('amount');
        $pastExpenses = $pastEntries->where('type', 'expense')->where('description', '!=', 'Salary')->sum('amount');
        $pastSalaries = \App\Models\DailySalaryEntry::whereDate('date', '<', $dateStr)->sum('amount');
        
        $openingBalance = $pastIncome - $pastExpenses - $pastSalaries;
        $closingBalance = $openingBalance + $totalIncome - $totalExpenses - $totalSalary;

        $acSales = $entries->where('description', 'A/c Sales')->sum('amount');
        $bankDeposit = $entries->where('description', 'Bank Deposit')->sum('amount');

        // --- FETCH HISTORY (Ledger Summaries) ---
        $ledgerEntries = \App\Models\DailyLedgerEntry::select('date')
            ->selectRaw('SUM(CASE WHEN type="income" AND description!="A/c Sales" THEN amount ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN type="expense" AND description!="Salary" THEN amount ELSE 0 END) as total_expense')
            ->selectRaw('SUM(CASE WHEN description="Bank Deposit" THEN amount ELSE 0 END) as bank_deposit')
            ->selectRaw('SUM(CASE WHEN description="A/c Sales" THEN amount ELSE 0 END) as ac_sales')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($item) {
                // Add salary total for this date
                $item->total_salary = \App\Models\DailySalaryEntry::whereDate('date', $item->date)->sum('amount');
                
                // Calculate balance (excluding A/C sales, including salary)
                $item->total = $item->total_income - $item->total_expense - $item->total_salary; 
                
                $firstEntry = \App\Models\DailyLedgerEntry::whereDate('date', $item->date)->first();
                $item->id = $firstEntry ? $firstEntry->id : 0;
                return $item;
            });

        // 4 Summary Totals for History Table
        $historySummary = [
            'total_income' => $ledgerEntries->sum('total_income'),
            'total_expense' => $ledgerEntries->sum('total_expense'),
            'total_salary' => $ledgerEntries->sum('total_salary'),
            'total_ac_balance' => $ledgerEntries->sum('ac_sales'),
        ];

        return view('reports.daily_ledger', compact(
            'date',
            'incomeEntries',
            'expenseEntries',
            'salaryEntries',
            'totalIncome',
            'totalExpenses',
            'totalSalary',
            'openingBalance',
            'closingBalance',
            'acSales',
            'bankDeposit',
            'ledgerEntries',
            'historySummary'
        ));
    }

    public function updateDailyLedger(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'entries' => 'nullable|array',
            'entries.*.id' => 'nullable', // Can be null for new entries
            'entries.*.description' => 'required_without:entries.*.id|string',
            'entries.*.amount' => 'required|numeric',
            'entries.*.type' => 'required_without:entries.*.id|in:income,expense',
            'salaries' => 'nullable|array',
            'salaries.*.id' => 'nullable|exists:daily_salary_entries,id',
            'salaries.*.employee_name' => 'required|string',
            'salaries.*.amount' => 'required|numeric',
        ]);

        if ($request->has('entries')) {
            foreach ($request->entries as $entryData) {
                if (isset($entryData['id']) && !str_starts_with($entryData['id'], 'new_')) {
                    \App\Models\DailyLedgerEntry::where('id', $entryData['id'])->update(['amount' => $entryData['amount']]);
                } else {
                    // Create new entry
                    \App\Models\DailyLedgerEntry::create([
                        'date' => $request->date,
                        'description' => $entryData['description'],
                        'amount' => $entryData['amount'],
                        'type' => $entryData['type']
                    ]);
                }
            }
        }

        // Handle salary entries
        if ($request->has('salaries')) {
            foreach ($request->salaries as $salaryData) {
                if (isset($salaryData['id']) && $salaryData['id']) {
                    // Update existing salary entry
                    \App\Models\DailySalaryEntry::where('id', $salaryData['id'])->update([
                        'employee_name' => $salaryData['employee_name'],
                        'amount' => $salaryData['amount']
                    ]);
                } else {
                    // Create new salary entry
                    \App\Models\DailySalaryEntry::create([
                        'date' => $request->date,
                        'employee_name' => $salaryData['employee_name'],
                        'amount' => $salaryData['amount']
                    ]);
                }
            }
        }

        return redirect()->route('reports.daily-ledger', ['date' => $request->date])->with('success', 'Daily Ledger updated successfully');
    }

    // Keeping store for backward compatibility if needed, but primary is now update
    public function storeDailyLedgerEntry(Request $request)
    {
        // Re-route to update if array is present? No, this was for single entry.
        // We can keep it or deprecate it.
        return $this->updateDailyLedger($request); 
    }

    public function destroyDailyLedgerEntry($id)
    {
        // The user's view calls deleteEntry(id). 
        // If the ID represents a single row, we delete just that?
        // OR if the ID represents a DAY (from the history table), we should delete/reset the whole day.
        // Given the history table context, "Delete" likely means "Clear this Day".
        
        $entry = \App\Models\DailyLedgerEntry::find($id);
        if ($entry) {
            $date = $entry->date;
            // Delete ALL entries for this date? Or just reset amounts to 0?
            // Usually "Delete Ledger" implies clearing the data.
            // Let's delete all entries for that date to be safe, or just the non-default ones?
            // Simplest: Reset amounts to 0 for default heads, delete others. 
            // BUT, if we delete, they regenerate on next view.
            
            // Let's just reset amounts to 0 for this date.
            \App\Models\DailyLedgerEntry::whereDate('date', $date)->update(['amount' => 0]);
            
            return redirect()->route('reports.daily-ledger')->with('success', 'Daily Ledger cleared for ' . $date);
        }

        return back()->with('error', 'Entry not found');
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

    public function dailyLedgerHistory(Request $request)
    {
        $query = \App\Models\DailyLedgerEntry::select('date')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense')
            ->selectRaw('SUM(CASE WHEN description = "A/c Sales" THEN amount ELSE 0 END) as ac_sales')
            ->selectRaw('SUM(CASE WHEN description = "Bank Deposit" THEN amount ELSE 0 END) as bank_deposit')
            ->groupBy('date');

        if ($request->from_date) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $records = $query->orderBy('date', 'desc')->get();

        if ($request->has('export')) {
             if ($request->export == 'excel') {
                 // Inline Export Class for simplicity or create separate if complex
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DailyLedgerHistoryExport($records), 'daily_ledger_history.xlsx');
            } elseif ($request->export == 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily_ledger_history_pdf', compact('records'));
                return $pdf->download('daily_ledger_history.pdf');
            }
        }

        return view('reports.daily_ledger_history', compact('records'));
    }

    public function getDailyLedgerDetails($date)
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $entries = \App\Models\DailyLedgerEntry::whereDate('date', $dateStr)->get();
        
        $income = $entries->where('type', 'income')->values();
        $expense = $entries->where('type', 'expense')->values();
        $salaries = \App\Models\DailySalaryEntry::whereDate('date', $dateStr)->get();

        return response()->json([
            'date' => $dateStr,
            'income' => $income,
            'expense' => $expense,
            'salaries' => $salaries,
            'total_income' => $income->sum('amount'),
            'total_expense' => $expense->sum('amount'),
            'total_salary' => $salaries->sum('amount')
        ]);
    }
}
