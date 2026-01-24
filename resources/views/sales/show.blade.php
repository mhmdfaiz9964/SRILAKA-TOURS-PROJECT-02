@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-gray-800 mb-0">Invoice #{{ $sale->invoice_number }}</h4>
            <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light shadow-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-5">
                        <div>
                            <h5 class="fw-bold text-primary">{{ config('app.name', 'Company Name') }}</h5>
                            <p class="small text-muted mb-0">123 Business Street</p>
                            <p class="small text-muted mb-0">City, Country</p>
                            <p class="small text-muted">Phone: +123456789</p>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-bold text-muted">Bill To:</h6>
                            <h5 class="fw-bold mb-1">{{ $sale->customer->full_name }}</h5>
                            <p class="small text-muted mb-0">{{ $sale->customer->company_name }}</p>
                            <p class="small text-muted">{{ $sale->customer->mobile_number }}</p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 5%;">#</th>
                                    <th style="width: 45%;">Preoduct Description</th>
                                    <th class="text-center" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 15%;">Unit Price</th>
                                    <th class="text-end" style="width: 10%;">Disc %</th>
                                    <th class="text-end pe-3" style="width: 15%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $index => $item)
                                <tr>
                                    <td class="ps-3">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold small">{{ $item->product->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $item->product->code }}</div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }} {{ $item->product->units }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ $item->discount_percentage > 0 ? $item->discount_percentage . '%' : '-' }}</td>
                                    <td class="text-end pe-3 fw-bold">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-end fw-bold text-muted">Subtotal:</td>
                                    <td class="text-end fw-bold">{{ number_format($sale->total_amount + $sale->discount_amount, 2) }}</td>
                                </tr>
                                @if($sale->discount_amount > 0)
                                <tr>
                                    <td class="text-end fw-bold text-muted">Round Off Discount:</td>
                                    <td class="text-end text-danger">-{{ number_format($sale->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="text-end h5 fw-bold">Total:</td>
                                    <td class="text-end h5 fw-bold text-primary">{{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-muted">Paid:</td>
                                    <td class="text-end text-success">{{ number_format($sale->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-muted">Balance Due:</td>
                                    <td class="text-end text-danger">{{ number_format(max(0, $sale->total_amount - $sale->paid_amount), 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($sale->notes)
                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted small mb-1 fw-bold">Notes:</p>
                        <p class="text-muted small fst-italic">{{ $sale->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3">
             <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Status</h6>
                    <div class="mb-3">
                        @if($sale->status == 'paid')
                            <div class="p-2 bg-success-subtle text-success text-center rounded fw-bold border border-success-subtle">Fully Paid</div>
                        @elseif($sale->status == 'partial')
                            <div class="p-2 bg-warning-subtle text-warning text-center rounded fw-bold border border-warning-subtle">Partially Paid</div>
                        @else
                            <div class="p-2 bg-danger-subtle text-danger text-center rounded fw-bold border border-danger-subtle">Unpaid</div>
                        @endif
                    </div>
                    <hr>
                    <button class="btn btn-primary w-100 mb-2">Add Payment</button>
                    <!-- Logic for adding payment modal could go here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
