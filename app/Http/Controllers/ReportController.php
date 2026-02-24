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

        // Handle Filtering
        $filter = $request->get('filter', 'all');
        $fromDate = $request->get('from_from_date');
        $toDate = $request->get('to_date');

        $query = \App\Models\DailyLedgerEntry::select('date')
            ->selectRaw('SUM(CASE WHEN type="income" AND description!="A/c Sales" THEN amount ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN type="expense" AND description!="Salary" THEN amount ELSE 0 END) as total_expense')
            ->selectRaw('SUM(CASE WHEN description="Bank Deposit" THEN amount ELSE 0 END) as bank_deposit')
            ->selectRaw('SUM(CASE WHEN description="A/c Sales" THEN amount ELSE 0 END) as ac_sales')
            ->groupBy('date');

        // Apply Date Filters
        if ($filter == 'today') {
            $query->where('date', now()->toDateString());
        } elseif ($filter == 'last_7_days') {
            $query->where('date', '>=', now()->subDays(7)->toDateString());
        } elseif ($filter == 'last_week') {
            $query->whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
        } elseif ($filter == 'last_month') {
            $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year);
        } elseif ($filter == 'last_year') {
            $query->whereYear('date', now()->subYear()->year);
        } elseif ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }

        $perPage = $request->get('per_page', 10);
        if ($perPage === 'all') {
            $perPage = 1000000;
        }

        $ledgerEntries = $query->orderBy('date', 'desc')->paginate($perPage)->withQueryString();

        $ledgerEntries->getCollection()->transform(function ($item) {
            $dateStr = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            $item->date_str = $dateStr;
            $item->total_salary = \App\Models\DailySalaryEntry::where('date', $dateStr)->sum('amount');
            $item->total = $item->total_income - $item->total_expense - $item->total_salary;
            $firstEntry = \App\Models\DailyLedgerEntry::where('date', $dateStr)->first();
            $item->id = $firstEntry ? $firstEntry->id : 0;
            return $item;
        });

        // Summary Totals
        $historySummary = [
            'total_income' => $ledgerEntries->sum('total_income'),
            'total_expense' => $ledgerEntries->sum('total_expense'),
            'total_salary' => $ledgerEntries->sum('total_salary'),
            'total_bank_deposit' => $ledgerEntries->sum('bank_deposit'),
            'total_ac_balance' => $ledgerEntries->sum('ac_sales'),
            'balance' => $ledgerEntries->sum('total'),
        ];

        // Check for Export
        if ($request->has('export')) {
            if ($request->export == 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DailyLedgerHistoryExport($ledgerEntries), 'daily_ledger_history.xlsx');
            } elseif ($request->export == 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily_ledger_history_pdf', ['records' => $ledgerEntries]);
                return $pdf->download('daily_ledger_history.pdf');
            }
        }

        return view('reports.daily_ledger', compact(
            'ledgerEntries',
            'historySummary',
            'filter'
        ));
    }

    public function getDailyLedgerDetails($date)
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $entries = \App\Models\DailyLedgerEntry::where('date', $dateStr)->get();

        $income = $entries->where('type', 'income')->values();
        $expense = $entries->where('type', 'expense')->values();
        $salaries = \App\Models\DailySalaryEntry::where('date', $dateStr)->get();

        return response()->json([
            'date' => $dateStr,
            'income' => $income,
            'expense' => $expense,
            'salaries' => $salaries,
            'total_income' => $income->where('description', '!=', 'A/c Sales')->sum('amount'),
            'total_expense' => $expense->sum('amount'),
            'total_salary' => $salaries->sum('amount')
        ]);
    }


    public function editDailyLedgerEntry($id)
    {
        $entry = \App\Models\DailyLedgerEntry::find($id);
        if (!$entry) {
            return redirect()->route('reports.daily-ledger.history')->with('error', 'Entry not found');
        }

        // Reuse the dailyLedger logic by passing the date and setting is_edit to true
        return $this->dailyLedger(new Request(['date' => $entry->date, 'is_edit' => true]));
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
            \App\Models\DailyLedgerEntry::where('date', $date)
                ->whereNotIn('id', $submittedEntryIds)
                ->delete();

            \App\Models\DailySalaryEntry::where('date', $date)
                ->whereNotIn('id', $submittedSalaryIds)
                ->delete();
        });

        return redirect()->route('reports.daily-ledger')->with('success', 'Daily Ledger updated successfully');
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
            \App\Models\DailyLedgerEntry::where('date', $date)->delete();
            \App\Models\DailySalaryEntry::where('date', $date)->delete();

            return redirect()->route('reports.daily-ledger.history')->with('success', 'Daily Ledger data completely removed for ' . $date);
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

        $entries = \App\Models\BalanceSheetEntry::where('date', $dateStr)->get();

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

        // --- History Section (Integrated) ---
        $query = \App\Models\BalanceSheetEntry::select('date')
            ->selectRaw('SUM(CASE WHEN category="asset" THEN amount ELSE 0 END) as total_assets')
            ->selectRaw('SUM(CASE WHEN category="liability" THEN amount ELSE 0 END) as total_liabilities')
            ->selectRaw('SUM(CASE WHEN category="equity" THEN amount ELSE 0 END) as total_equity')
            ->groupBy('date');

        $bsHistory = $query->orderBy('date', 'desc')->get()
            ->map(function ($item) {
                $item->total_liab_eq = $item->total_liabilities + $item->total_equity;
                $item->difference = $item->total_assets - $item->total_liab_eq;
                $firstRow = \App\Models\BalanceSheetEntry::where('date', $item->date)->first();
                $item->id = $firstRow ? $firstRow->id : 0;
                return $item;
            });

        $historySummary = [
            'total_assets' => $bsHistory->sum('total_assets'),
            'total_liab_eq' => $bsHistory->sum('total_liab_eq'),
        ];

        return view('reports.balance_sheet', compact(
            'date',
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'totalLiabilitiesAndEquity',
            'bsHistory',
            'historySummary'
        ));
    }

    public function editBalanceSheet($id)
    {
        $entry = \App\Models\BalanceSheetEntry::find($id);
        if (!$entry) {
            return redirect()->route('reports.balance-sheet')->with('error', 'Entry not found');
        }

        // Reuse the balanceSheet logic by passing the date
        return $this->balanceSheet(new Request(['date' => $entry->date]));
    }

    public function balanceSheetHistory(Request $request)
    {
        $query = \App\Models\BalanceSheetEntry::select('date')
            ->selectRaw('SUM(CASE WHEN category="asset" THEN amount ELSE 0 END) as total_assets')
            ->selectRaw('SUM(CASE WHEN category="liability" THEN amount ELSE 0 END) as total_liabilities')
            ->selectRaw('SUM(CASE WHEN category="equity" THEN amount ELSE 0 END) as total_equity')
            ->groupBy('date');

        if ($request->from_date) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('date', '<=', $request->to_date);
        }

        $bsHistory = $query->orderBy('date', 'desc')->get()
            ->map(function ($item) {
                $item->total_liab_eq = $item->total_liabilities + $item->total_equity;
                $item->difference = $item->total_assets - $item->total_liab_eq;
                $firstRow = \App\Models\BalanceSheetEntry::where('date', $item->date)->first();
                $item->id = $firstRow ? $firstRow->id : 0;
                return $item;
            });

        $historySummary = [
            'total_assets' => $bsHistory->sum('total_assets'),
            'total_liab_eq' => $bsHistory->sum('total_liab_eq'),
        ];

        return view('reports.balance_sheet_history', compact('bsHistory', 'historySummary'));
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
            \App\Models\BalanceSheetEntry::where('date', $date)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });

        return redirect()->route('reports.balance-sheet')->with('success', 'Balance Sheet updated successfully');
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
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('date', '<=', $request->to_date);
        }

        $records = $query->orderBy('date', 'desc')->get()
            ->map(function ($item) {
                // Fetch the first entry ID for this date to use as a handle for editing
                $firstEntry = \App\Models\DailyLedgerEntry::where('date', $item->date)->first();
                $item->id = $firstEntry ? $firstEntry->id : 0;
                return $item;
            });

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

    public function getBalanceSheetDetails($date)
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $entries = \App\Models\BalanceSheetEntry::where('date', $dateStr)->get();

        $assets = $entries->where('category', 'asset')->values();
        $liabilities = $entries->where('category', 'liability')->values();
        $equity = $entries->where('category', 'equity')->values();

        return response()->json([
            'date' => $dateStr,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $assets->sum('amount'),
            'total_liabilities' => $liabilities->sum('amount'),
            'total_equity' => $equity->sum('amount'),
            'total_liab_eq' => $liabilities->sum('amount') + $equity->sum('amount')
        ]);
    }

    public function destroyBalanceSheet($id)
    {
        $entry = \App\Models\BalanceSheetEntry::find($id);
        if ($entry) {
            $date = $entry->date;
            \App\Models\BalanceSheetEntry::where('date', $date)->delete();
            return redirect()->route('reports.balance-sheet')->with('success', 'Balance Sheet completely removed for ' . $date);
        }
        return back()->with('error', 'Entry not found');
    }
}
