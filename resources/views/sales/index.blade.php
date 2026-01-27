@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Sales Transactions</h4>
                <p class="text-muted small mb-0">Manage your sales, invoices, and payments</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> New Sale
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4" style="background: #eff6ff;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-bold text-uppercase">Total Sales</span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #fff; color: #3b82f6;">
                                <i class="fa-solid fa-sack-dollar"></i>
                            </div>
                        </div>
                        <div class="h4 fw-bold mb-0" style="color: #1e40af;">LKR {{ number_format($sales->sum('total_amount'), 2) }}</div>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4" style="background: #fff7ed;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-bold text-uppercase">Total Outstanding</span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #fff; color: #f97316;">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="h4 fw-bold mb-0" style="color: #9a3412;">LKR {{ number_format($sales->sum(function($s){ return $s->total_amount - $s->paid_amount; }), 2) }}</div>
                    </div>
                </div>
            </div>
             <!-- Status Filter Card (Placeholder for design or implementation) -->
             <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                         <div class="dropdown w-100">
                            <button class="btn btn-light w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown">
                                <span>{{ request('status') ? ucfirst(request('status')) : 'Filter by Status' }}</span>
                                <i class="fa-solid fa-chevron-down small"></i>
                            </button>
                            <ul class="dropdown-menu w-100 border-0 shadow-lg rounded-3">
                                <li><a class="dropdown-item" href="{{ route('sales.index') }}">All</a></li>
                                <li><a class="dropdown-item" href="{{ route('sales.index', ['status' => 'paid']) }}">Paid</a></li>
                                <li><a class="dropdown-item" href="{{ route('sales.index', ['status' => 'partial']) }}">Partial</a></li>
                                <li><a class="dropdown-item" href="{{ route('sales.index', ['status' => 'unpaid']) }}">Unpaid</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-filter me-1 text-black"></i> Filter
                    </button>
                    <!-- Search could go here -->
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ count($sales) }} Results</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase">Date</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Invoice #</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Customer</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Total Amount</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Paid / A/C</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('sales.show', $sale->id) }}'">
                            <td class="ps-4 text-muted small">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</td>
                            <td class="fw-bold text-dark small">{{ $sale->invoice_number }}</td>
                            <td class="small fw-semibold">{{ $sale->customer->full_name }}</td>
                            <td class="small fw-bold">{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="small">
                                <div class="text-success">{{ number_format($sale->paid_amount, 2) }}</div>
                                @if($sale->total_amount - $sale->paid_amount > 0)
                                    <div class="text-danger small" style="font-size: 0.75rem;">A/C: {{ number_format($sale->total_amount - $sale->paid_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="small">
                                @if($sale->status == 'paid')
                                    <span class="badge bg-success-subtle text-success rounded-pill border border-0">Paid</span>
                                @elseif($sale->status == 'partial')
                                    <span class="badge bg-warning-subtle text-warning rounded-pill border border-0">Partial</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill border border-0">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <!-- Add Edit/Delete if needed -->
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">No sales records found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .btn-icon:hover {
        background: #f3f4f6;
        color: #6366f1 !important;
    }
</style>
@endsection
