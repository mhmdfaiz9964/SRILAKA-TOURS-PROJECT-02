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



    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ route('sales.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="Inv # or Customer...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border bg-white dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center rounded-3 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 31px;">
                            Select Status
                        </button>
                        <ul class="dropdown-menu p-2 w-100 border-0 shadow-lg rounded-3">
                            <li><label class="dropdown-item d-flex align-items-center gap-2 cursor-pointer" onclick="event.stopPropagation()">
                                <input type="checkbox" name="status[]" value="paid" class="form-check-input" {{ in_array('paid', (array)request('status')) ? 'checked' : '' }}>
                                <span>Paid</span>
                            </label></li>
                            <li><label class="dropdown-item d-flex align-items-center gap-2 cursor-pointer" onclick="event.stopPropagation()">
                                <input type="checkbox" name="status[]" value="partial" class="form-check-input" {{ in_array('partial', (array)request('status')) ? 'checked' : '' }}>
                                <span>Partial</span>
                            </label></li>
                            <li><label class="dropdown-item d-flex align-items-center gap-2 cursor-pointer" onclick="event.stopPropagation()">
                                <input type="checkbox" name="status[]" value="unpaid" class="form-check-input" {{ in_array('unpaid', (array)request('status')) ? 'checked' : '' }}>
                                <span>Unpaid</span>
                            </label></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                     <label class="form-label small fw-bold text-muted mb-1">Date Range</label>
                     <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control border-light rounded-3 shadow-none" value="{{ request('start_date') }}">
                        <span class="input-group-text bg-transparent border-0 text-muted">-</span>
                        <input type="date" name="end_date" class="form-control border-light rounded-3 shadow-none" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('sales.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
                <!-- Sort Hidden -->
                <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $sales->total() }} Results</div>
                </div>
            </form>
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
