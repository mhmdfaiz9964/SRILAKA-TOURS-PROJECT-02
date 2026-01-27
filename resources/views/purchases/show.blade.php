@extends('layouts.app')

@section('content')
<div class="container-fluid d-print-none">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-gray-800 mb-0">Purchase Details #{{ $purchase->invoice_number ?? $purchase->id }}</h4>
             <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print Note
            </button>
             <a href="{{ route('purchases.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Print Layout -->
<div class="invoice-container bg-white p-5 mx-auto rounded shadow-sm" style="max-width: 800px; min-height: 1000px; color: black; font-family: 'Times New Roman', Times, serif;">
    
    <!-- Header -->
    <div class="border-bottom border-2 border-dark pb-2 mb-2">
        <div class="row align-items-center">
            <div class="col-8">
                <h2 class="fw-bold text-uppercase mb-1" style="color: #000080;">{{ config('app.name', 'Company Name') }}</h2>
                <p class="mb-0 small fw-bold">Purchase Order / Goods Receipt</p>
            </div>
            <div class="col-4 text-end">
                 <!-- Placeholder for Logo or Info -->
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-6 text-start">
               <span class="fw-bold">Ref No: {{ $purchase->invoice_number ?? $purchase->id }}</span> <br>
               @if($purchase->grn_number)
               <span class="fw-bold small">GRN: {{ $purchase->grn_number }}</span><br>
               @endif
               <span class="fw-bold text-uppercase badge border border-dark text-dark rounded-0">{{ $purchase->purchase_type ?? 'LOCAL' }}</span>
            </div>
            <div class="col-6 text-end">
                <span class="fw-bold">Date : {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Supplier Details -->
    <div class="mb-3">
        <div class="d-flex mb-1" style="border-bottom: 1px dotted #999;">
            <span class="fw-bold" style="width: 80px;">Supplier:</span>
            <span class="flex-grow-1 ps-2">{{ $purchase->supplier->full_name }}</span>
        </div>
        <div class="d-flex mb-1" style="border-bottom: 1px dotted #999;">
             <span class="fw-bold" style="width: 80px;">Company:</span>
            <span class="flex-grow-1 ps-2">{{ $purchase->supplier->company_name }}</span>
             <span class="fw-bold ms-3">Tel:</span>
            <span class="ps-2" style="width: 150px;">{{ $purchase->supplier->contact_number }}</span>
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-2">
        <table class="table table-bordered border-dark mb-0" style="font-size: 0.9rem;">
            <thead>
                <tr class="text-center bg-light">
                    <th style="width: 15%;">Item Code</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 15%;">Cost</th>
                    <th style="width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td class="text-center">{{ $item->product->code }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td>
                        {{ $item->product->name }}
                         @if($item->description)
                        <br><small class="text-muted fst-italic">({{ $item->description }})</small>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($item->cost_price, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
                @for($i = 0; $i < max(0, 8 - count($purchase->items)); $i++)
                <tr style="height: 25px;">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" rowspan="{{ 5 + ($purchase->investors->count() > 0 ? $purchase->investors->count() + 2 : 0) }}" class="align-top p-2 border-end border-dark">
                         <!-- Investors Section if exists -->
                         @if($purchase->investors->count() > 0)
                            <h6 class="fw-bold small text-decoration-underline mb-2">Investors</h6>
                            <table class="table table-sm table-borderless mb-0">
                                @foreach($purchase->investors as $inv)
                                <tr>
                                    <td class="p-0 small">{{ $inv->investor_name }}:</td>
                                    <td class="p-0 small fw-bold text-end">{{ number_format($inv->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </table>
                         @endif
                    </td>
                    <td class="text-end text-muted small p-1">Sub Total Item</td>
                    <td class="text-end fw-bold p-1">{{ number_format($purchase->items->sum('total_price'), 2) }}</td>
                </tr>
                 <tr>
                    <td class="text-end text-muted small p-1">Transport</td>
                    <td class="text-end p-1">{{ number_format($purchase->transport_cost, 2) }}</td>
                </tr>
                 <tr>
                    <td class="text-end text-muted small p-1">Broker</td>
                    <td class="text-end p-1">{{ number_format($purchase->broker_cost, 2) }}</td>
                </tr>
                 <tr>
                    <td class="text-end text-muted small p-1">Duty + Kuli</td>
                    <td class="text-end p-1">{{ number_format($purchase->duty_cost + $purchase->kuli_cost, 2) }}</td>
                </tr>
                <tr style="background: #eee;">
                    <td class="text-end fw-bold p-1 h6 mb-0">Grand Total</td>
                    <td class="text-end fw-bold p-1 h6 mb-0">{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                <tr>

                    <td class="text-end fw-bold p-1">Paid</td>
                    <td class="text-end fw-bold p-1">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>

                    <td class="text-end fw-bold p-1 h5 mb-0">Balance</td>
                    <td class="text-end fw-bold p-1 h5 mb-0">{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatures -->
    <div class="row mt-5 pt-4">
        <div class="col-6 text-center">
            <div style="border-top: 1px dotted #000; width: 60%; margin: 0 auto;"></div>
            <p class="small fw-bold mt-1">Authorized By</p>
        </div>
        <div class="col-6 text-center">
            <div style="border-top: 1px dotted #000; width: 60%; margin: 0 auto;"></div>
            <p class="small fw-bold mt-1">Received By / Entered By</p>
        </div>
    </div>
    
     <div class="mt-4 pt-2 border-top border-dark text-muted small text-end" style="font-size: 10px;">
        Generated on {{ date('Y-m-d H:i:s') }}
    </div>
</div>

<style>
    @media print {
        body {
            background: white;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        .container-fluid, nav, footer, .btn {
            display: none !important;
        }
        .invoice-container {
            box-shadow: none !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            border: none !important;
        }
    }
</style>
@endsection
