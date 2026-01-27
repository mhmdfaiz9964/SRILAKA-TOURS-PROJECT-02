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
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-filter me-1 text-black"></i> Filter
                    </button>
                    <!-- Search could go here -->
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ count($purchases) }} Results</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase">Date</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Inv / GRN</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Type</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Supplier</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Total Amount</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Paid / Due</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('purchases.show', $purchase->id) }}'">
                            <td class="ps-4 text-muted small">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</td>
                            <td class="fw-bold text-dark small">
                                {{ $purchase->invoice_number ?? '-' }}
                                @if($purchase->grn_number)
                                    <div class="text-muted" style="font-size: 0.7rem;">GRN: {{ $purchase->grn_number }}</div>
                                @endif
                            </td>
                            <td class="small">
                                <span class="badge bg-{{ $purchase->purchase_type == 'import' ? 'info' : 'secondary' }}-subtle text-{{ $purchase->purchase_type == 'import' ? 'info' : 'secondary' }} rounded-pill border-0 text-uppercase" style="font-size: 0.65rem;">
                                    {{ $purchase->purchase_type ?? 'local' }}
                                </span>
                            </td>
                            <td class="small fw-semibold">{{ $purchase->supplier->full_name }}</td>
                            <td class="small fw-bold">{{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="small">
                                <div class="text-success">{{ number_format($purchase->paid_amount, 2) }}</div>
                                @if($purchase->total_amount - $purchase->paid_amount > 0)
                                    <div class="text-danger small" style="font-size: 0.75rem;">Due: {{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</div>
                                @endif
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
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
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
@endsection
