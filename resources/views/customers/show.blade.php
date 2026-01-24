@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-gray-800">Customer Profile</h4>
    </div>

    <div class="row">
        <!-- Customer Info Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="avatar-circle mx-auto bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                            {{ substr($customer->full_name, 0, 1) }}
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $customer->full_name }}</h5>
                    <p class="text-muted small mb-3">{{ $customer->company_name ?? 'Individual' }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <a href="tel:{{ $customer->mobile_number }}" class="btn btn-sm btn-light rounded-pill px-3">
                            <i class="fa-solid fa-phone me-1"></i> Call
                        </a>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-light rounded-pill px-3">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </a>
                    </div>

                    <div class="text-start border-top pt-3 mt-3">
                        <div class="mb-2">
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">MOBILE</small>
                            <span class="small fw-semibold">{{ $customer->mobile_number }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">CREDIT LIMIT</small>
                            <span class="small fw-semibold">{{ number_format($customer->credit_limit, 2) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">STATUS</small>
                            @if($customer->status)
                                <span class="badge bg-success-subtle text-success rounded-pill border border-0">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill border border-0">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger / Activities -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-pill" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active rounded-pill fw-bold small" data-bs-toggle="tab" href="#ledger">
                                <i class="fa-solid fa-book me-1"></i> Ledger
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill fw-bold small" data-bs-toggle="tab" href="#sales">
                                <i class="fa-solid fa-cart-shopping me-1"></i> Sales History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill fw-bold small" data-bs-toggle="tab" href="#payments">
                                <i class="fa-solid fa-money-bill-wave me-1"></i> Payments
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- Ledger Tab -->
                        <div class="tab-pane fade show active" id="ledger">
                            <div class="alert alert-light border border-dashed text-center">
                                <p class="mb-0 text-muted small">Ledger calculation logic would go here showing Debit/Credit history.</p>
                                <!-- Placeholder for Ledger Table -->
                            </div>
                        </div>

                        <!-- Sales Tab -->
                        <div class="tab-pane fade" id="sales">
                             <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 small fw-bold">Date</th>
                                            <th class="small fw-bold">Invoice #</th>
                                            <th class="small fw-bold">Total</th>
                                            <th class="small fw-bold">Status</th>
                                            <th class="text-end pe-3 small fw-bold">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->sales as $sale)
                                        <tr>
                                            <td class="ps-3 small">{{ $sale->sale_date }}</td>
                                            <td class="small fw-bold">{{ $sale->invoice_number }}</td>
                                            <td class="small">{{ number_format($sale->total_amount, 2) }}</td>
                                            <td class="small">
                                                <span class="badge bg-{{ $sale->status == 'paid' ? 'success' : ($sale->status == 'partial' ? 'warning' : 'danger') }}-subtle text-{{ $sale->status == 'paid' ? 'success' : ($sale->status == 'partial' ? 'warning' : 'danger') }} border-0">
                                                    {{ ucfirst($sale->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-light"><i class="fa-regular fa-eye"></i></a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted small py-3">No sales found</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="payments">
                            <div class="alert alert-light border border-dashed text-center">
                                <p class="mb-0 text-muted small">Payment history would be listed here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
