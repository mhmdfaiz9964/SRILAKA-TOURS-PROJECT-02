<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyLedgerEntry;
use App\Models\DailySalaryEntry;
use App\Models\BalanceSheetEntry;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function dailyLedger(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = DailyLedgerEntry::select('date')
            ->selectRaw('SUM(CASE WHEN type="income" AND description!="A/c Sales" THEN amount ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN type="expense" AND description!="Salary" THEN amount ELSE 0 END) as total_expense')
            ->selectRaw('SUM(CASE WHEN description="Bank Deposit" THEN amount ELSE 0 END) as bank_deposit')
            ->selectRaw('SUM(CASE WHEN description="A/c Sales" THEN amount ELSE 0 END) as ac_sales')
            ->groupBy('date');

        if ($filter == 'today') $query->where('date', now()->toDateString());
        elseif ($filter == 'last_7_days') $query->where('date', '>=', now()->subDays(7)->toDateString());
        elseif ($filter == 'last_month') $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year);
        if ($request->from_date) $query->where('date', '>=', $request->from_date);
        if ($request->to_date) $query->where('date', '<=', $request->to_date);

        $entries = $query->orderBy('date', 'desc')->paginate($request->get('per_page', 15));
        $entries->getCollection()->transform(function($item) {
            $dateStr = Carbon::parse($item->date)->format('Y-m-d');
            $item->total_salary = DailySalaryEntry::where('date', $dateStr)->sum('amount');
            $item->total = $item->total_income - $item->total_expense - $item->total_salary;
            $firstEntry = DailyLedgerEntry::where('date', $dateStr)->first();
            $item->id = $firstEntry ? $firstEntry->id : 0;
            return $item;
        });

        return response()->json(['data' => $entries->items(), 'meta' => ['current_page' => $entries->currentPage(), 'last_page' => $entries->lastPage(), 'total' => $entries->total()],
            'summary' => ['total_income' => $entries->sum('total_income'), 'total_expense' => $entries->sum('total_expense'), 'total_salary' => $entries->sum('total_salary'), 'balance' => $entries->sum('total')]]);
    }

    public function getDailyLedgerDetails($date)
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $entries = DailyLedgerEntry::where('date', $dateStr)->get();
        $salaries = DailySalaryEntry::where('date', $dateStr)->get();
        return response()->json(['date' => $dateStr, 'income' => $entries->where('type', 'income')->values(), 'expense' => $entries->where('type', 'expense')->values(), 'salaries' => $salaries,
            'total_income' => $entries->where('type', 'income')->where('description', '!=', 'A/c Sales')->sum('amount'), 'total_expense' => $entries->where('type', 'expense')->sum('amount'), 'total_salary' => $salaries->sum('amount')]);
    }

    public function updateDailyLedger(Request $request)
    {
        $request->validate(['date' => 'required|date', 'entries' => 'nullable|array', 'salaries' => 'nullable|array']);
        \DB::transaction(function() use ($request) {
            $date = $request->date;
            $entryIds = []; $salaryIds = [];
            if ($request->has('entries')) {
                foreach ($request->entries as $entryData) {
                    if (!empty($entryData['id']) && is_numeric($entryData['id'])) {
                        DailyLedgerEntry::where('id', $entryData['id'])->update(['description' => $entryData['description'], 'amount' => $entryData['amount'], 'type' => $entryData['type']]);
                        $entryIds[] = $entryData['id'];
                    } else {
                        $new = DailyLedgerEntry::create(['date' => $date, 'description' => $entryData['description'], 'amount' => $entryData['amount'], 'type' => $entryData['type']]);
                        $entryIds[] = $new->id;
                    }
                }
            }
            if ($request->has('salaries')) {
                foreach ($request->salaries as $salaryData) {
                    if (!empty($salaryData['id']) && is_numeric($salaryData['id'])) {
                        DailySalaryEntry::where('id', $salaryData['id'])->update(['employee_name' => $salaryData['employee_name'], 'amount' => $salaryData['amount']]);
                        $salaryIds[] = $salaryData['id'];
                    } else {
                        $new = DailySalaryEntry::create(['date' => $date, 'employee_name' => $salaryData['employee_name'], 'amount' => $salaryData['amount']]);
                        $salaryIds[] = $new->id;
                    }
                }
            }
            DailyLedgerEntry::where('date', $date)->whereNotIn('id', $entryIds)->delete();
            DailySalaryEntry::where('date', $date)->whereNotIn('id', $salaryIds)->delete();
        });
        return response()->json(['message' => 'Daily Ledger updated']);
    }

    public function destroyDailyLedgerEntry($id)
    {
        $entry = DailyLedgerEntry::find($id);
        if ($entry) { $date = $entry->date; DailyLedgerEntry::where('date', $date)->delete(); DailySalaryEntry::where('date', $date)->delete(); return response()->json(['message' => 'Daily Ledger removed for ' . $date]); }
        return response()->json(['message' => 'Entry not found'], 404);
    }

    public function balanceSheet(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : now();
        $dateStr = $date->format('Y-m-d');
        $defaultHeads = ['asset' => ['Customer Outstanding', 'Cheque in Hand', 'RTN Cheque', 'Stock in Cost'], 'liability' => ['Supplier Out', 'Investors'], 'equity' => ['Profit/Lost']];
        foreach ($defaultHeads as $type => $heads) { foreach ($heads as $head) { BalanceSheetEntry::firstOrCreate(['date' => $dateStr, 'name' => $head, 'category' => $type], ['amount' => 0]); } }

        $entries = BalanceSheetEntry::where('date', $dateStr)->get();
        $assets = $entries->where('category', 'asset'); $liabilities = $entries->where('category', 'liability'); $equity = $entries->where('category', 'equity');

        return response()->json(['date' => $dateStr, 'assets' => $assets->values(), 'liabilities' => $liabilities->values(), 'equity' => $equity->values(),
            'total_assets' => $assets->sum('amount'), 'total_liabilities' => $liabilities->sum('amount'), 'total_equity' => $equity->sum('amount'), 'total_liab_eq' => $liabilities->sum('amount') + $equity->sum('amount')]);
    }

    public function getBalanceSheetDetails($date)
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $entries = BalanceSheetEntry::where('date', $dateStr)->get();
        return response()->json(['date' => $dateStr, 'assets' => $entries->where('category', 'asset')->values(), 'liabilities' => $entries->where('category', 'liability')->values(), 'equity' => $entries->where('category', 'equity')->values(),
            'total_assets' => $entries->where('category', 'asset')->sum('amount'), 'total_liabilities' => $entries->where('category', 'liability')->sum('amount'), 'total_equity' => $entries->where('category', 'equity')->sum('amount')]);
    }

    public function updateBalanceSheet(Request $request)
    {
        $request->validate(['date' => 'required|date', 'entries' => 'nullable|array']);
        \DB::transaction(function() use ($request) {
            $date = $request->date; $ids = [];
            if ($request->has('entries')) {
                foreach ($request->entries as $entryData) {
                    if (!empty($entryData['id']) && is_numeric($entryData['id'])) {
                        BalanceSheetEntry::where('id', $entryData['id'])->update(['name' => $entryData['name'], 'amount' => $entryData['amount'], 'category' => $entryData['category']]);
                        $ids[] = $entryData['id'];
                    } else {
                        $new = BalanceSheetEntry::create(['date' => $date, 'name' => $entryData['name'], 'amount' => $entryData['amount'], 'category' => $entryData['category']]);
                        $ids[] = $new->id;
                    }
                }
            }
            BalanceSheetEntry::where('date', $date)->whereNotIn('id', $ids)->delete();
        });
        return response()->json(['message' => 'Balance Sheet updated']);
    }

    public function destroyBalanceSheet($id)
    {
        $entry = BalanceSheetEntry::find($id);
        if ($entry) { $date = $entry->date; BalanceSheetEntry::where('date', $date)->delete(); return response()->json(['message' => 'Balance Sheet removed for ' . $date]); }
        return response()->json(['message' => 'Entry not found'], 404);
    }
}
