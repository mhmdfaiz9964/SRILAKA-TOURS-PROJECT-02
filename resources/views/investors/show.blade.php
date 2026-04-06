@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('investors.index') }}" class="btn btn-sm btn-light rounded-circle shadow-none">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0">{{ $investor->name }}</h4>
                <span class="badge rounded-pill px-2 py-1 bg-{{ $investor->status == 'active' ? 'success' : 'secondary' }} small">
                    {{ ucfirst($investor->status) }}
                </span>
            </div>
            <p class="text-muted small ms-5">Investor detailed performance and transaction history (Ledger)</p>
        </div>
        <div class="d-flex gap-2">
            @can('investor-edit')
            <a href="{{ route('investors.edit', $investor) }}" class="btn btn-outline-primary btn-sm px-3 rounded-3 shadow-none">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            @endcan
            <button class="btn btn-primary btn-sm px-4 rounded-3 shadow-none" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print Ledger
            </button>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f8fafc; border-left: 4px solid #6366f1 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Invested</div>
                    <div class="h4 fw-bold mb-0 text-dark">LKR {{ number_format($totalInvested, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #ecfdf5; border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Payouts</div>
                    <div class="h4 fw-bold mb-0 text-success">LKR {{ number_format($totalReturned, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2; border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Current Balance</div>
                    @php $balance = $totalInvested - $totalReturned; @endphp
                    <div class="h4 fw-bold mb-0 {{ $balance > 0 ? 'text-danger' : 'text-muted' }}">LKR {{ number_format($balance, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Profit History</div>
                    <div class="h4 fw-bold mb-0 text-warning">LKR {{ number_format($investor->paid_profit, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0">Transaction History (Ledger)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small text-uppercase">Date</th>
                        <th class="py-3 text-muted small text-uppercase">Description</th>
                        <th class="py-3 text-muted small text-uppercase">Ref #</th>
                        <th class="py-3 text-muted small text-uppercase text-end">Debit (+)</th>
                        <th class="py-3 text-muted small text-uppercase text-end">Credit (-)</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                     <tr>
                        <td class="ps-4 small text-muted">{{ \Carbon\Carbon::parse($investor->created_at)->format('d/m/Y') }}</td>
                        <td class="small fw-bold">Initial Account Opening Value</td>
                        <td class="small text-muted">-</td>
                        <td class="text-end small fw-bold text-primary">{{ number_format($investor->invest_amount, 2) }}</td>
                        <td class="text-end small">-</td>
                        <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($investor->invest_amount, 2) }}</td>
                    </tr>
                    @php $currentBal = $investor->invest_amount; @endphp
                    @forelse($ledger as $row)
                        @php $currentBal += ($row['debit'] - $row['credit']); @endphp
                        <tr>
                            <td class="ps-4 small text-muted">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                            <td class="small">
                                <div class="fw-bold">{{ $row['description'] }}</div>
                                <div class="ultra-small text-muted">{{ ucfirst($row['type']) }} Transaction</div>
                            </td>
                            <td class="small text-muted">{{ $row['ref'] }}</td>
                            <td class="text-end small fw-bold {{ $row['debit'] > 0 ? 'text-primary' : '' }}">
                                {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                            </td>
                            <td class="text-end small fw-bold {{ $row['credit'] > 0 ? 'text-success' : '' }}">
                                {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}
                            </td>
                            <td class="text-end pe-4 fw-bold {{ $currentBal > 0 ? 'text-danger' : 'text-dark' }}">
                                LKR {{ number_format($currentBal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <!-- Only initial -->
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Additional Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Investor Information</h6>
                    <div class="row mb-2">
                        <div class="col-6 text-muted small fw-bold">Expectation Profit:</div>
                        <div class="col-6 small fw-bold">LKR {{ number_format($investor->expect_profit, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted small fw-bold">Collect Date:</div>
                        <div class="col-6 small">{{ $investor->collect_date ?: 'Not Set' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted small fw-bold">Refund Date:</div>
                        <div class="col-6 small text-danger fw-bold">{{ $investor->refund_date ?: 'Not Set' }}</div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12 text-muted small fw-bold">Remarks / Notes:</div>
                        <div class="col-12 mt-1 small bg-light p-3 rounded-3 fst-italic">
                            {{ $investor->notes ?: 'No additional notes provided.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ultra-small { font-size: 0.7rem; }
    @media print {
        .navbar, .sidebar, .btn, .d-none.d-print-block, footer { display: none !important; }
        .container-fluid { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>
@endsection
