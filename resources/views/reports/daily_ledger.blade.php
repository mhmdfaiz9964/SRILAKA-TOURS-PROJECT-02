@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Daily Ledger</h4>
            <span class="text-muted small">Day Book / Cash Book</span>
        </div>
        <form action="{{ route('reports.daily-ledger') }}" method="GET" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control form-control-sm border-light rounded-3 shadow-none">
            <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">View</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Opening Balance -->
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f8fafc;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Opening Balance (B/F)</div>
                    <div class="fw-bold text-dark fs-5">LKR {{ number_format($openingBalance, 2) }}</div>
                </div>
            </div>
        </div>
        <!-- Income -->
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                <div class="card-body p-3">
                    <div class="text-success small fw-bold text-uppercase mb-1">Total Daily Income</div>
                    <div class="fw-bold text-success fs-5">+ LKR {{ number_format($totalIncome, 2) }}</div>
                </div>
            </div>
        </div>
        <!-- Expenses -->
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                <div class="card-body p-3">
                    <div class="text-danger small fw-bold text-uppercase mb-1">Total Money Out</div>
                    <div class="fw-bold text-danger fs-5">- LKR {{ number_format($totalExpenses + $todaysSupplierPayments, 2) }}</div>
                </div>
            </div>
        </div>
        <!-- Closing Balance -->
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #eff6ff;">
                <div class="card-body p-3">
                    <div class="text-primary small fw-bold text-uppercase mb-1">Closing Balance</div>
                    <div class="fw-bold text-primary fs-5">LKR {{ number_format($closingBalance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Money In Section -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-arrow-down text-success small"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Income (Money In)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-10">
                                <tr>
                                    <th class="ps-4 py-2 small text-muted">Source</th>
                                    <th class="py-2 small text-muted text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-medium text-dark">Cash Sales</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Cash receipts from sales</div>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($cashSales, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-medium text-dark">Other Receipts</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Cheques, Bank Transfers, Collections</div>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-dark">
                                        LKR {{ number_format($totalIncome - $cashSales, 2) }}
                                    </td>
                                </tr>
                                <!-- Detailed Breakdown (Optional) -->
                                @foreach($todaysPayments->where('type', 'in') as $payment)
                                <tr class="bg-light bg-opacity-10">
                                    <td class="ps-5 text-muted small" style="border-left: 2px solid #10b981;">
                                        <i class="fa-solid fa-angle-right me-1" style="font-size: 0.6rem;"></i>
                                        {{ $payment->payable->name ?? $payment->payable->full_name ?? 'Customer' }} ({{ ucfirst($payment->payment_method) }})
                                    </td>
                                    <td class="text-end pe-4 text-muted small">
                                        {{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-success bg-opacity-10">
                                <tr>
                                    <td class="ps-4 fw-bold text-success">Total Income</td>
                                    <td class="pe-4 text-end fw-bold text-success">LKR {{ number_format($totalIncome, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Money Out Section -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                     <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-arrow-up text-danger small"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Expenses (Money Out)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-10">
                                <tr>
                                    <th class="ps-4 py-2 small text-muted">Category</th>
                                    <th class="py-2 small text-muted text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Supplier Payments</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($todaysSupplierPayments, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Salary</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($salary, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Transport / Distribution</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($transport, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Food / Welfare</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($food, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Bank Deposit</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($bankDeposit, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Other Expenses</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($otherExpenses, 2) }}</td>
                                </tr>
                                 <!-- Detailed Breakdown -->
                                @foreach($expenses as $expense)
                                <tr class="bg-light bg-opacity-10">
                                    <td class="ps-5 text-muted small" style="border-left: 2px solid #ef4444;">
                                        <i class="fa-solid fa-angle-right me-1" style="font-size: 0.6rem;"></i>
                                        {{ $expense->reason }}
                                        @if($expense->paid_by) <span class="text-secondary opacity-75">({{ $expense->paid_by }})</span> @endif
                                    </td>
                                    <td class="text-end pe-4 text-muted small">
                                        {{ number_format($expense->amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-danger bg-opacity-10">
                                <tr>
                                    <td class="ps-4 fw-bold text-danger">Total Expenses</td>
                                    <td class="pe-4 text-end fw-bold text-danger">LKR {{ number_format($totalExpenses + $todaysSupplierPayments, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
