@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Purchase Transactions</h4>
                <p class="text-muted small mb-0">Manage your purchases from suppliers</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> New Purchase
                </a>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ route('purchases.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Date Range</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control border-light rounded-3 shadow-none" value="{{ request('start_date') }}">
                        <span class="input-group-text bg-transparent border-0 text-muted">-</span>
                        <input type="date" name="end_date" class="form-control border-light rounded-3 shadow-none" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Supplier</label>
                    <select name="supplier_id" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Types</option>
                        <option value="local" {{ request('type') == 'local' ? 'selected' : '' }}>Local</option>
                        <option value="import" {{ request('type') == 'import' ? 'selected' : '' }}>Import</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Sort By</label>
                    <select name="sort" class="form-select form-select-sm border-light rounded-3">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="highest_amount" {{ request('sort') == 'highest_amount' ? 'selected' : '' }}>Highest Amount</option>
                        <option value="lowest_amount" {{ request('sort') == 'lowest_amount' ? 'selected' : '' }}>Lowest Amount</option>
                        <option value="name_az" {{ request('sort') == 'name_az' ? 'selected' : '' }}>Supplier (A-Z)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase">Date</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">GRN</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Type</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Supplier</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Total Amount</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Paid</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Due</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('purchases.show', $purchase->id) }}'">
                            <td class="ps-4 text-muted small">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</td>
                            <td class="fw-bold text-dark small">
                                {{ $purchase->grn_number ?? '-' }}
                            </td>
                            <td class="small">
                                <span class="badge bg-{{ $purchase->purchase_type == 'import' ? 'info' : 'secondary' }}-subtle text-{{ $purchase->purchase_type == 'import' ? 'info' : 'secondary' }} rounded-pill border-0 text-uppercase" style="font-size: 0.65rem;">
                                    {{ $purchase->purchase_type ?? 'local' }}
                                </span>
                            </td>
                            <td class="small fw-semibold">{{ $purchase->supplier->full_name }}</td>
                            <td class="small fw-bold">{{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="small fw-bold text-success">{{ number_format($purchase->paid_amount, 2) }}</td>
                            <td class="small fw-bold {{ ($purchase->total_amount - $purchase->paid_amount) > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}
                            </td>
                            <td class="small">
                                @if($purchase->status == 'paid')
                                    <span class="badge bg-success-subtle text-success rounded-pill border border-0">Paid</span>
                                @elseif($purchase->status == 'partial')
                                    <span class="badge bg-warning-subtle text-warning rounded-pill border border-0">Partial</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill border border-0">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    @can('purchase-edit')
                                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('purchase-delete')
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-danger" onclick="confirmDelete({{ $purchase->id }}, 'delete-purchase-{{ $purchase->id }}')">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                    <form id="delete-purchase-{{ $purchase->id }}" action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">No purchase records found.</div>
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

<script>
    function confirmDelete(id, formId) {
        Swal.fire({
            title: 'Delete Purchase?',
            text: "Are you sure you want to delete this purchase record? This checks for related stock items too.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        })
    }
</script>
@endsection
