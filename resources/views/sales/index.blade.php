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
                @can('sale-create')
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> New Sale
                </a>
                @endcan
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4" style="background: #eff6ff;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-bold text-uppercase">Filtered Sales</span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #fff; color: #3b82f6;">
                                <i class="fa-solid fa-sack-dollar"></i>
                            </div>
                        </div>
                        <div class="h4 fw-bold mb-0" style="color: #1e40af;">LKR {{ number_format($totalSales, 2) }}</div>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4" style="background: #fff7ed;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-bold text-uppercase">Filtered Outstanding</span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #fff; color: #f97316;">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="h4 fw-bold mb-0" style="color: #9a3412;">LKR {{ number_format($totalOutstanding, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('sales.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Invoice or Customer..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Sort By</label>
                    <select name="sort" class="form-select form-select-sm border-0 bg-light">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="highest_amount" {{ request('sort') == 'highest_amount' ? 'selected' : '' }}>Highest Amount</option>
                        <option value="lowest_amount" {{ request('sort') == 'lowest_amount' ? 'selected' : '' }}>Lowest Amount</option>
                        <option value="name_az" {{ request('sort') == 'name_az' ? 'selected' : '' }}>Customer (A-Z)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm border-0 bg-light">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">From Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm border-0 bg-light" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">To Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm border-0 bg-light" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                    <select name="status" class="form-select form-select-sm border-0 bg-light">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3" style="background: #6366f1; border: none;"><i class="fa-solid fa-filter"></i></button>
                        <a href="{{ route('sales.index') }}" class="btn btn-light btn-sm w-100 rounded-3 border-0"><i class="fa-solid fa-rotate"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Recent Invoices</h6>
            <span class="text-muted small">{{ $sales->total() }} Results</span>
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
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted small">No sales records found matching your criteria.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($sales->hasPages())
                <div class="p-4 border-top">
                    {{ $sales->links() }}
                </div>
            @endif
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
