@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-gray-800 mb-0">Purchase Details</h4>
             <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d F Y') }}</p>
        </div>
        <div>
             <a href="{{ route('purchases.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row w-100">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-4">
                        <div>
                            <h6 class="fw-bold text-muted text-uppercase small">Supplier Info</h6>
                            <h5 class="fw-bold mb-1">{{ $purchase->supplier->full_name }}</h5>
                            <p class="small text-muted mb-0">{{ $purchase->supplier->company_name }}</p>
                            <p class="small text-muted">{{ $purchase->supplier->contact_number }}</p>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-bold text-muted text-uppercase small">Reference</h6>
                            <h5 class="fw-bold mb-1">{{ $purchase->invoice_number ?? 'N/A' }}</h5>
                            <span class="badge bg-light text-dark border">Purchase ID: #{{ $purchase->id }}</span>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 50%;">Item</th>
                                    <th class="text-center" style="width: 15%;">Qty</th>
                                    <th class="text-end" style="width: 15%;">Cost</th>
                                    <th class="text-end pe-3" style="width: 20%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold small">{{ $item->product->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $item->product->code }}</div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->cost_price, 2) }}</td>
                                    <td class="text-end pe-3 fw-bold">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm table-borderless">
                                <tr class="border-top">
                                    <td class="text-end h5 fw-bold">Total:</td>
                                    <td class="text-end h5 fw-bold text-primary">{{ number_format($purchase->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-muted">Paid:</td>
                                    <td class="text-end text-success">{{ number_format($purchase->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-muted">Balance:</td>
                                    <td class="text-end text-danger">{{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
         <div class="col-lg-3">
             <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Payment Status</h6>
                    <div class="mb-3">
                        @if($purchase->status == 'paid')
                            <div class="p-2 bg-success-subtle text-success text-center rounded fw-bold border border-success-subtle">Fully Paid</div>
                        @elseif($purchase->status == 'partial')
                            <div class="p-2 bg-warning-subtle text-warning text-center rounded fw-bold border border-warning-subtle">Partially Paid</div>
                        @else
                            <div class="p-2 bg-danger-subtle text-danger text-center rounded fw-bold border border-danger-subtle">Unpaid</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
