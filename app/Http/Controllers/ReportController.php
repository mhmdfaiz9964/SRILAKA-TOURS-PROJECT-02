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

        // --- Income (Money In) ---
        // 1. Cash Sales (Sales made today with cash payment OR Payments of type 'cash' for sales made today)
        // Let's assume Payments table is the source of money movement.
        // Cash Sales: Payments received today, method=cash, where the related sale was also today.
        
        $todaysPayments = Payment::whereDate('payment_date', $dateStr)->get();
        
        $cashSales = $todaysPayments->filter(function($payment) use ($dateStr) {
             // Check if payment is for a Sale and Sale date is today and method is cash
             // If payment->payable is Customer, assume it's for a Sale.
             // If payment_method is cash.
             // Ideally we check specific Sale date if possible. But payment doesn't always link to concise Sale if it's bulk.
             // Let's simplify: Cash Sales = Payments of method 'cash' from Customers.
             return $payment->payment_method == 'cash' && $payment->type == 'in';
        })->sum('amount');

        // A/C Sales: Payments received today via Cheque/Bank Transfer?
        // Or does user mean "Old Payments"?
        // User list: A/C Sales, Cash Sales, Old Payments.
        // Interpretation:
        // Cash Sales: Cash received.
        // A/C Sales: Credit Sales made today (NOT Money In)? But user put it under "Income (Money In)". 
        // This is contradictory. "Income" = Cash Flow. Credit Sale is NOT Cash Flow.
        // Maybe "A/C Sales" means "Collections fron Account Sales"? i.e. Debtors paying.
        // Let's define:
        // - Cash Sales: Cash receipts.
        // - Old Payments: Receipts for old debts?
        // - A/C Sales: Maybe just "Sales on Account" (Credit) for info? But it says "Added to get Total Daily Income".
        // If I add Credit Sales to Income, Total Income will be inflated vs Cash.
        // I will assume "A/C Sales" here means "Cheque/Bank receipts" (Non-cash receipts).
        
        // Let's try:
        // Cash Income = Cash Sales + Cash Collections (Old Payments).
        // Maybe "Old Payments" = Collections from previous days' sales.
        // "Cash Sales" = Collections from today's sales.
        
        $totalIncome = $todaysPayments->where('type', 'in')->sum('amount');
        
        // Let's just group payments by method for display if we can't perfectly separate "Old" vs "New" without Sale link.
        // But I will try to follow the requested structure:
        // "A/C Sales" -> I'll map this to "Bank/Cheque/Credit" type receipts?
        // "Cash Sales" -> 'cash' receipts.
        // "Old Payments" -> Hard to distinguish without link to Sale Date. 
        // I will display "Total Income" as sum of all payments 'in'.
        // And breakdown by method/type.
        
        // --- Expenses (Money Out) ---
        $expenses = Expense::whereDate('expense_date', $dateStr)->get();
        // Break down by reason aliases or exact grouping
        $salary = $expenses->filter(fn($e) => stripos($e->reason, 'salary') !== false)->sum('amount');
        $transport = $expenses->filter(fn($e) => stripos($e->reason, 'transport') !== false || stripos($e->reason, 'distribution') !== false)->sum('amount');
        $food = $expenses->filter(fn($e) => stripos($e->reason, 'food') !== false || stripos($e->reason, 'welfare') !== false)->sum('amount');
        $bankDeposit = $expenses->filter(fn($e) => stripos($e->reason, 'deposit') !== false)->sum('amount');
        
        $otherExpenses = $expenses->sum('amount') - ($salary + $transport + $food + $bankDeposit);
        $totalExpenses = $expenses->sum('amount');
        
        // --- Daily Cash Summary ---
        // Opening Balance: This is hard without a stored ledger. 
        // Simple approach: Previous Day's Closing Balance.
        // Recursive calculation is too heavy.
        // Alternative: Sum of ALL 'in' payments < date - Sum of ALL 'expenses' < date?
        // This is heavy but accurate if history is clean.
        
        $pastIncome = Payment::whereDate('payment_date', '<', $dateStr)->where('type', 'in')->sum('amount');
        $pastExpenses = Expense::whereDate('expense_date', '<', $dateStr)->sum('amount');
        // We also need to account for Supplier Payments (Money Out) which are in `payments` table with type='out'.
        $pastSupplierPayments = Payment::whereDate('payment_date', '<', $dateStr)->where('type', 'out')->sum('amount');
        
        $pastTotalOut = $pastExpenses + $pastSupplierPayments;
        $openingBalance = $pastIncome - $pastTotalOut;

        $todaysSupplierPayments = Payment::whereDate('payment_date', $dateStr)->where('type', 'out')->sum('amount');
        $totalDailyExpensesReal = $totalExpenses + $todaysSupplierPayments; // Expenses + Supplier Payments
        
        $closingBalance = $openingBalance + $totalIncome - $totalDailyExpensesReal;

        return view('reports.daily_ledger', compact(
            'date',
            'cashSales', // Just total cash?
            'totalIncome',
            'expenses',
            'salary', 'transport', 'food', 'bankDeposit', 'otherExpenses', 'totalExpenses',
            'openingBalance', 'closingBalance', 'todaysPayments', 'todaysSupplierPayments'
        ));
    }

    public function balanceSheet(Request $request) 
    {
        $date = $request->date ? Carbon::parse($request->date) : now();
        // Snapshots are ideally at end of day.
        
        // ASSETS
        // Customer Outstanding
        // Naive calculation: To date, Total Sales - Total Payments (In).
        // Warning: This assumes all payments in `payments` table are for Sales.
        $totalSales = Sale::whereDate('sale_date', '<=', $date)->sum('total_amount');
        $totalReceived = Payment::whereDate('payment_date', '<=', $date)->where('type', 'in')->sum('amount');
        $customerOutstanding = $totalSales - $totalReceived;

        // Cheques in Hand (Received but not deposited/realized?)
        // Assumed InCheque status 'received' means in hand.
        $chequesInHand = InCheque::where('status', 'received')->sum('amount'); 

        // Returned Cheques
        $returnedCheques = InCheque::where('status', 'returned')->sum('amount');

        // Stock at Cost
        $stockAtCost = Product::sum(DB::raw('current_stock * cost_price'));

        // Other Assets? (Maybe Bank Balance? But not requested explicitly)
        $totalAssets = $customerOutstanding + $chequesInHand + $returnedCheques + $stockAtCost;


        // LIABILITIES
        // Supplier Outstanding
        $totalPurchases = Purchase::whereDate('purchase_date', '<=', $date)->sum('total_amount');
        $totalPaid = Payment::whereDate('payment_date', '<=', $date)->where('type', 'out')->sum('amount');
        $supplierOutstanding = $totalPurchases - $totalPaid;

        // Investors
        $investors = Investor::sum('invest_amount');

        // Other Liabilities?

        // Profit / Loss (Running Balance to make equation fit)
        // Assets = Liabilities + Equity
        // Equity = Investors + Retained Earnings (P&L)
        // P&L = Assets - Liabilities - Investors
        $profitOrLoss = $totalAssets - ($supplierOutstanding + $investors);

        $totalLiabilitiesAndEquity = $supplierOutstanding + $investors + $profitOrLoss;

        return view('reports.balance_sheet', compact(
            'date',
            'customerOutstanding',
            'chequesInHand',
            'returnedCheques',
            'stockAtCost',
            'totalAssets',
            'supplierOutstanding',
            'investors',
            'profitOrLoss',
            'totalLiabilitiesAndEquity'
        ));
    }
}
