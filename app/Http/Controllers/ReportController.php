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
                    'date',
                    'entries',
                    'totalIncome',
                    'totalExpenses',
                    'closingBalance'
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
            'Tour Advance',
            'Tour Final Payment',
            'Other Income', // Old Income
            'Fuel',
            'Driver Bata',
            'Highway Ticket',
            'Parking',
            'Office Expenses',
            'Salaries',
            'Other Expenses' // Old Expense
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

        // 4. FETCH HISTORY (Ledger Summaries) - Ensuring unique dates and correct IDs
        $ledgerEntries = \App\Models\DailyLedgerEntry::select('date')
            ->selectRaw('SUM(CASE WHEN type="income" AND description!="A/c Sales" THEN amount ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN type="expense" AND description!="Salary" THEN amount ELSE 0 END) as total_expense')
            ->selectRaw('SUM(CASE WHEN description="Bank Deposit" THEN amount ELSE 0 END) as bank_deposit')
            ->selectRaw('SUM(CASE WHEN description="A/c Sales" THEN amount ELSE 0 END) as ac_sales')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item) {
                // Ensure date is a string Y-m-d for reliable URL usage
                $dateStr = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                $item->date_str = $dateStr;

                // Add salary total for this date
                $item->total_salary = \App\Models\DailySalaryEntry::whereDate('date', $dateStr)->sum('amount');

                // Calculate balance (excluding A/C sales, including salary)
                $item->total = $item->total_income - $item->total_expense - $item->total_salary;

                $firstEntry = \App\Models\DailyLedgerEntry::whereDate('date', $dateStr)->first();
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
            'entries.*.id' => 'nullable',
            'entries.*.description' => 'required|string',
            'entries.*.amount' => 'required|numeric',
            'entries.*.type' => 'required|in:income,expense',
            'salaries' => 'nullable|array',
            'salaries.*.id' => 'nullable',
            'salaries.*.employee_name' => 'required|string',
            'salaries.*.amount' => 'required|numeric',
        ]);

        \DB::transaction(function () use ($request) {
            $date = $request->date;
            $submittedEntryIds = [];
            $submittedSalaryIds = [];

            // 1. Process Normal Entries (Income/Expense)
            if ($request->has('entries')) {
                foreach ($request->entries as $entryData) {
                    if (!empty($entryData['id']) && is_numeric($entryData['id'])) {
                        // Update existing
                        \App\Models\DailyLedgerEntry::where('id', $entryData['id'])->update([
                            'description' => $entryData['description'],
                            'amount' => $entryData['amount'],
                            'type' => $entryData['type']
                        ]);
                        $submittedEntryIds[] = $entryData['id'];
                    } else {
                        // Create new
                        $newEntry = \App\Models\DailyLedgerEntry::create([
                            'date' => $date,
                            'description' => $entryData['description'],
                            'amount' => $entryData['amount'],
                            'type' => $entryData['type']
                        ]);
                        $submittedEntryIds[] = $newEntry->id;
                    }
                }
            }

            // 2. Process Salary Entries
            if ($request->has('salaries')) {
                foreach ($request->salaries as $salaryData) {
                    if (!empty($salaryData['id']) && is_numeric($salaryData['id'])) {
                        // Update existing
                        \App\Models\DailySalaryEntry::where('id', $salaryData['id'])->update([
                            'employee_name' => $salaryData['employee_name'],
                            'amount' => $salaryData['amount']
                        ]);
                        $submittedSalaryIds[] = $salaryData['id'];
                    } else {
                        // Create new
                        $newSalary = \App\Models\DailySalaryEntry::create([
                            'date' => $date,
                            'employee_name' => $salaryData['employee_name'],
                            'amount' => $salaryData['amount']
                        ]);
                        $submittedSalaryIds[] = $newSalary->id;
                    }
                }
            }

            // 3. Remove records that were deleted in the UI
            // Important: We only remove entries for THIS date that weren't submitted.
            // But we MUST NOT remove default heads if they are missing? 
            // Standard approach: if user removed it, it's gone.
            \App\Models\DailyLedgerEntry::whereDate('date', $date)
                ->whereNotIn('id', $submittedEntryIds)
                ->delete();

            \App\Models\DailySalaryEntry::whereDate('date', $date)
                ->whereNotIn('id', $submittedSalaryIds)
                ->delete();
        });

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
        $entry = \App\Models\DailyLedgerEntry::find($id);
        if ($entry) {
            $date = $entry->date;

            // Delete ALL entries and salary entries for this date
            \App\Models\DailyLedgerEntry::whereDate('date', $date)->delete();
            \App\Models\DailySalaryEntry::whereDate('date', $date)->delete();

            return redirect()->route('reports.daily-ledger', ['date' => $date])->with('success', 'Daily Ledger data completely removed for ' . $date);
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
                    'date',
                    'assets',
                    'liabilities',
                    'equity',
                    'totalAssets',
                    'totalLiabilities',
                    'totalEquity',
                    'totalLiabilitiesAndEquity'
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
            'date' => 'required|date',
            'entries' => 'nullable|array',
            // Entries can have an ID (update) or no ID (create)
            'entries.*.id' => 'nullable',
            'entries.*.name' => 'required|string',
            'entries.*.amount' => 'required|numeric',
            'entries.*.category' => 'required|in:asset,liability,equity',
        ]);

        \DB::transaction(function () use ($request) {
            $date = $request->date;
            $submittedIds = [];

            if ($request->has('entries')) {
                foreach ($request->entries as $entryData) {
                    if (!empty($entryData['id']) && is_numeric($entryData['id'])) {
                        // Update existing
                        \App\Models\BalanceSheetEntry::where('id', $entryData['id'])->update([
                            'name' => $entryData['name'],
                            'amount' => $entryData['amount'],
                            'category' => $entryData['category']
                        ]);
                        $submittedIds[] = $entryData['id'];
                    } else {
                        // Create new or duplicated
                        $newEntry = \App\Models\BalanceSheetEntry::create([
                            'date' => $date,
                            'name' => $entryData['name'],
                            'amount' => $entryData['amount'],
                            'category' => $entryData['category']
                        ]);
                        $submittedIds[] = $newEntry->id;
                    }
                }
            }

            // Optional: Delete entries that were removed from the UI
            // Only delete for this specific date
            \App\Models\BalanceSheetEntry::whereDate('date', $date)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });

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
