<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cheque;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Investor;
use App\Models\Customer;
use App\Models\InCheque;

class DashboardController extends Controller
{
    public function index()
    {
        // Pending Cheques (RTN cheques not paid)
        $pendingCheques = Cheque::where('payment_status', '!=', 'paid')->count();
        $pendingChequesAmount = Cheque::where('payment_status', '!=', 'paid')->sum('amount');

        // Pending Payments (Sales that are not fully paid)
        $pendingPayments = Sale::whereIn('status', ['unpaid', 'partial'])->whereNull('original_sale_id')->count();
        $pendingPaymentsAmount = Sale::whereIn('status', ['unpaid', 'partial'])->whereNull('original_sale_id')
            ->selectRaw('SUM(total_amount - paid_amount) as total')
            ->value('total') ?? 0;

        // Stock Alert Products (low stock)
        $stockAlertProducts = Product::whereColumn('current_stock', '<=', 'stock_alert')->count();

        // Total Investors
        $totalInvestors = Investor::count();
        $totalInvestorAmount = Investor::where('status', 'active')->sum('invest_amount');

        // Outstanding Amount (Customer outstanding)
        $outstandingAmount = Customer::withSum('sales', 'total_amount')
            ->withSum('payments', 'amount')
            ->get()
            ->sum(function ($c) {
                return max(0, ($c->sales_sum_total_amount ?? 0) - ($c->payments_sum_amount ?? 0));
            });

        // Pending A/C Amount
        $allSales = Sale::sum('total_amount');
        $allPaid = Sale::sum('paid_amount');
        $pendingAcAmount = $allSales - $allPaid;

        // In Cheques Stats
        $inChequeStats = [
            'in_hand' => InCheque::where('status', 'received')->count(),
            'in_hand_amount' => InCheque::where('status', 'received')->sum('amount'),
            'deposited' => InCheque::where('status', 'deposited')->count(),
            'overdue' => InCheque::whereIn('status', ['received', 'deposited'])
                ->where('cheque_date', '<', now()->toDateString())
                ->count(),
        ];

        // Recent Sales
        $recentSales = Sale::with('customer')
            ->whereNull('original_sale_id')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'invoice_number' => $s->invoice_number,
                'customer_name' => $s->customer->full_name ?? '-',
                'total_amount' => $s->total_amount,
                'paid_amount' => $s->paid_amount,
                'status' => $s->status,
                'sale_date' => $s->sale_date,
            ]);

        return response()->json([
            'pending_cheques' => ['count' => $pendingCheques, 'amount' => $pendingChequesAmount],
            'pending_payments' => ['count' => $pendingPayments, 'amount' => $pendingPaymentsAmount],
            'stock_alert_products' => ['count' => $stockAlertProducts],
            'total_investors' => ['count' => $totalInvestors, 'amount' => $totalInvestorAmount],
            'outstanding_amount' => ['amount' => $outstandingAmount],
            'pending_ac_amount' => ['amount' => $pendingAcAmount],
            'in_cheque_stats' => $inChequeStats,
            'recent_sales' => $recentSales,
        ]);
    }
}
