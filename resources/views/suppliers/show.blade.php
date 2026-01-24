@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-gray-800">Supplier Profile</h4>
    </div>

    <div class="row">
        <!-- Supplier Info Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="avatar-circle mx-auto bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                            {{ substr($supplier->full_name, 0, 1) }}
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $supplier->full_name }}</h5>
                    <p class="text-muted small mb-3">{{ $supplier->company_name ?? 'Individual' }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <a href="tel:{{ $supplier->contact_number }}" class="btn btn-sm btn-light rounded-pill px-3">
                            <i class="fa-solid fa-phone me-1"></i> Call
                        </a>
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-light rounded-pill px-3">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </a>
                    </div>

                    <div class="text-start border-top pt-3 mt-3">
                        <div class="mb-2">
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">CONTACT</small>
                            <span class="small fw-semibold">{{ $supplier->contact_number }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">STATUS</small>
                            @if($supplier->status)
                                <span class="badge bg-success-subtle text-success rounded-pill border border-0">active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill border border-0">inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases History -->
        <div class="col-md-9">
             <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 py-3 px-4">
                    <h5 class="fw-bold mb-0">Purchase History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 small fw-bold">Date</th>
                                    <th class="py-3 small fw-bold">Invoice #</th>
                                    <th class="py-3 small fw-bold">Total</th>
                                    <th class="py-3 small fw-bold">Status</th>
                                    <th class="text-end pe-4 py-3 small fw-bold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplier->purchases as $purchase)
                                <tr>
                                    <td class="ps-4 small">{{ $purchase->purchase_date }}</td>
                                    <td class="small fw-bold">{{ $purchase->invoice_number ?? '-' }}</td>
                                    <td class="small">{{ number_format($purchase->total_amount, 2) }}</td>
                                    <td class="small">
                                        <span class="badge bg-{{ $purchase->status == 'paid' ? 'success' : ($purchase->status == 'partial' ? 'warning' : 'danger') }}-subtle text-{{ $purchase->status == 'paid' ? 'success' : ($purchase->status == 'partial' ? 'warning' : 'danger') }} border-0">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-light"><i class="fa-regular fa-eye"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted small py-4">No purchases found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
