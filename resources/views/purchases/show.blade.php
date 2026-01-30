@extends('layouts.app')

@section('content')
<!-- Screen Layout: Visible only on screen -->
<div class="container-fluid d-print-none">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Purchase Details</h1>
    </div>

    <div class="row">
        <!-- Supplier/Purchase Summary Sidebar (Left side like Customer show) -->
        <div class="col-lg-3">
             <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="avatar-circle mx-auto bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; font-size: 1.5rem;">
                            {{ substr($purchase->supplier->full_name ?? 'P', 0, 1) }}
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $purchase->supplier->full_name ?? 'Walk-in' }}</h5>
                    <p class="text-muted small mb-3">{{ $purchase->supplier->company_name ?? 'Purchase Record' }}</p>
                    
                    <div class="text-start border-top pt-3 mt-3">
                        <div class="mb-2">
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">TOTAL AMOUNT</small>
                            <span class="small fw-bold text-primary">{{ number_format($purchase->total_amount, 2) }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">PAID AMOUNT</small>
                            <span class="small fw-bold text-success">{{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.7rem;">STATUS</small>
                            @php
                                $statusClass = [
                                    'unpaid' => 'bg-danger-subtle text-danger',
                                    'partial' => 'bg-warning-subtle text-warning',
                                    'paid' => 'bg-success-subtle text-success'
                                ][$purchase->status] ?? 'bg-secondary-subtle text-secondary';
                            @endphp
                            <span class="badge {{ $statusClass }} rounded-pill border-0">{{ ucfirst($purchase->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary rounded-pill shadow-sm" onclick="window.print()">
                            <i class="fa-solid fa-print me-2"></i> Print Note
                        </button>
                        @if($purchase->status !== 'paid')
                        <button class="btn btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fa-solid fa-plus me-2"></i> Add Payment
                        </button>
                        @endif
                        <a href="{{ route('purchases.index') }}" class="btn btn-light rounded-pill">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Details (Right side like Customer show) -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                             <h6 class="fw-bold text-uppercase text-muted small mb-1">Purchase Details</h6>
                             <h4 class="fw-bold mb-0">GRN: {{ $purchase->grn_number ?? 'N/A' }}</h4>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block">Purchase Date</span>
                            <span class="fw-bold">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                       <table class="table table-hover align-middle">
                           <thead class="bg-light">
                               <tr>
                                   <th class="ps-3">Product</th>
                                   <th>Cost</th>
                                   <th class="text-center">Qty</th>
                                   <th class="text-end pe-3">Total</th>
                               </tr>
                           </thead>
                           <tbody>
                               @foreach($purchase->items as $item)
                               <tr>
                                   <td class="ps-3">
                                       <div class="fw-bold">{{ $item->product->name }}</div>
                                       <div class="text-muted small">Code: {{ $item->product->code }}</div>
                                   </td>
                                   <td>{{ number_format($item->cost_price, 2) }}</td>
                                   <td class="text-center">{{ $item->quantity }}</td>
                                   <td class="text-end pe-3 fw-bold">{{ number_format($item->total_price, 2) }}</td>
                               </tr>
                               @endforeach
                           </tbody>
                       </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end">
                             @if($purchase->investors->count() > 0)
                                <h6 class="fw-bold text-muted text-uppercase small mb-3">Investors Information</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        @foreach($purchase->investors as $investor)
                                        <tr>
                                            <td class="small py-1 text-muted">{{ $investor->investor_name }}</td>
                                            <td class="small py-1 fw-bold text-end">Rs. {{ number_format($investor->amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                             @endif
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-end text-muted small">Subtotal Item:</td>
                                    <td class="text-end fw-bold small">Rs. {{ number_format($purchase->items->sum('total_price'), 2) }}</td>
                                </tr>
                                @php
                                    $additionalCosts = [
                                        'Transport' => $purchase->transport_cost,
                                        'Broker' => $purchase->broker_cost,
                                        'Loading' => $purchase->loading_cost,
                                        'Unloading' => $purchase->unloading_cost,
                                        'Labour' => $purchase->labour_cost,
                                        'Air Ticket' => $purchase->air_ticket_cost,
                                        'Other' => $purchase->other_expenses,
                                    ];
                                @endphp
                                @foreach($additionalCosts as $label => $val)
                                    @if($val > 0)
                                    <tr>
                                        <td class="text-end text-muted small">{{ $label }}:</td>
                                        <td class="text-end fw-bold small">Rs. {{ number_format($val, 2) }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr class="border-top">
                                    <td class="text-end fw-bold h5">Grand Total:</td>
                                    <td class="text-end fw-bold h5 text-primary">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end text-muted small fw-bold">Paid:</td>
                                    <td class="text-end text-success fw-bold">Rs. {{ number_format($purchase->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end text-muted small fw-bold">Balance:</td>
                                    <td class="text-end text-danger fw-bold h6">Rs. {{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Payment History Section -->
                    <div class="mt-4 border-top pt-4">
                        <h6 class="fw-bold mb-3">Payment History</h6>
                        @if($purchase->payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->payments as $payment)
                                        <tr>
                                            <td class="small">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                                            <td class="text-uppercase small fw-bold">
                                                {{ str_replace('_', ' ', $payment->payment_method) }}
                                                @if($payment->payment_method == 'cheque' && $payment->cheque_id)
                                                    <span class="d-block text-muted fw-normal" style="font-size: 0.7rem;">#{{ $payment->payment_cheque_number }}</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-danger">Rs. {{ number_format($payment->amount, 2) }}</td>
                                            <td class="text-muted small">{{ $payment->notes }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted small fst-italic">No payment records found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
             <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold mb-3">Payment Status</h6>
                    <div class="mb-3">
                        @if($purchase->status == 'paid')
                            <div class="p-2 bg-success-subtle text-success rounded fw-bold border border-success-subtle">Fully Paid</div>
                        @elseif($purchase->status == 'partial')
                            <div class="p-2 bg-warning-subtle text-warning rounded fw-bold border border-warning-subtle">Partially Paid</div>
                        @else
                            <div class="p-2 bg-danger-subtle text-danger rounded fw-bold border border-danger-subtle">Unpaid</div>
                        @endif
                    </div>
                    
                    <h6 class="fw-bold mb-3 mt-4 text-start">Reference</h6>
                    <p class="text-start small text-muted mb-1">Ref No: <strong>{{ $purchase->invoice_number ?? $purchase->id }}</strong></p>
                    @if($purchase->grn_number)
                    <p class="text-start small text-muted">GRN: <strong>{{ $purchase->grn_number }}</strong></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Layout: Strict Format from Image -->
<div class="invoice-wrapper d-none d-print-block">
    <div class="invoice-container bg-white mx-auto" style="width: 100%; max-width: 800px; color: black; font-family: 'Times New Roman', Times, serif;">
        
        <!-- Header -->
        <div class="border-bottom border-2 border-dark pb-2 mb-2">
            <div class="row align-items-center">
                <div class="col-8">
                    <h2 class="fw-bold text-uppercase mb-1" style="color: #000080; font-size: 2.5rem;">{{ $globalSettings['company_name'] ?? config('app.name') }}</h2>
                    <p class="mb-0 small fw-bold">Purchase Order / Goods Receipt</p>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-6 text-start">
                   <div class="fw-bold">Ref No: {{ $purchase->invoice_number ?? $purchase->id }}</div>
                   <div class="fw-bold">GRN: {{ $purchase->grn_number ?? '-' }}</div>
                   <div class="mt-1"><span class="fw-bold text-uppercase border border-dark px-2 py-1 small">{{ $purchase->purchase_type ?? 'LOCAL' }}</span></div>
                </div>
                <div class="col-6 text-end">
                    <span class="fw-bold">Date : {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Supplier Section -->
        <div class="mb-3 border-bottom border-dark pb-2" style="font-size: 1rem;">
            <div class="row g-1">
                <div class="col-2 fw-bold border-bottom border-dark border-1 mb-1 pb-1">Supplier:</div>
                <div class="col-10 border-bottom border-dark border-1 mb-1 pb-1">{{ $purchase->supplier->full_name }}</div>
                
                <div class="col-2 fw-bold border-bottom border-dark border-1 pb-1">Company:</div>
                <div class="col-6 border-bottom border-dark border-1 pb-1">{{ $purchase->supplier->company_name ?? '-' }}</div>
                <div class="col-1 fw-bold border-bottom border-dark border-1 pb-1">Tel:</div>
                <div class="col-3 border-bottom border-dark border-1 pb-1">{{ $purchase->supplier->contact_number }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered border-dark mb-0 w-100" style="font-size: 1rem; table-layout: fixed;">
            <thead>
                <tr class="text-center">
                    <th style="width: 130px;">Item Code</th>
                    <th style="width: 70px;">Qty</th>
                    <th style="width: 300px;">Description</th>
                    <th style="width: 100px;">Cost</th>
                    <th style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td class="text-center">{{ $item->product->code }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td>
                        {{ $item->product->name }}
                        @if($item->description) <br><small class="text-muted fst-italic">({{ $item->description }})</small> @endif
                    </td>
                    <td class="text-end">{{ number_format($item->cost_price, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
                @for($i = 0; $i < max(0, 10 - count($purchase->items)); $i++)
                <tr style="height: 30px;">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
            <tfoot>
                @php
                    $additionalCosts = [
                        ['label' => 'Sub Total Item', 'val' => $purchase->items->sum('total_price'), 'bold' => false],
                        ['label' => 'Transport', 'val' => $purchase->transport_cost, 'bold' => false],
                        ['label' => 'Broker', 'val' => $purchase->broker_cost, 'bold' => false],
                        ['label' => 'Loading', 'val' => $purchase->loading_cost, 'bold' => false],
                        ['label' => 'Unloading', 'val' => $purchase->unloading_cost, 'bold' => false],
                        ['label' => 'Labour Charges', 'val' => $purchase->labour_cost, 'bold' => false],
                        ['label' => 'Air Ticket', 'val' => $purchase->air_ticket_cost, 'bold' => false],
                        ['label' => 'Other Expenses', 'val' => $purchase->other_expenses, 'bold' => false],
                    ];
                    $totalRows = count($additionalCosts) + 3; // + Grand Total, Paid, Balance
                @endphp

                @foreach($additionalCosts as $index => $cost)
                <tr>
                    @if($index === 0)
                    <td colspan="3" rowspan="{{ $totalRows }}" class="align-top p-2" style="border-bottom: 2px solid #000;">
                        <h6 class="fw-bold text-decoration-underline small mb-2">Investors</h6>
                        <table class="table table-sm table-borderless mb-0">
                            @foreach($purchase->investors as $investor)
                            <tr>
                                <td class="p-0 small" style="width: 60%;">{{ $investor->investor_name }}:</td>
                                <td class="p-0 small fw-bold text-end">{{ number_format($investor->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                    @endif
                    <td class="text-end p-1 border-dark border-1" style="font-size: 0.9rem;">{{ $cost['label'] }}</td>
                    <td class="text-end p-1 border-dark border-1 {{ $cost['bold'] ? 'fw-bold' : '' }}" style="font-size: 0.9rem;">{{ number_format($cost['val'], 2) }}</td>
                </tr>
                @endforeach

                <tr style="background: #f1f3f4 !important; -webkit-print-color-adjust: exact;">
                    <td class="text-end p-1 fw-bold border-dark border-1" style="font-size: 1rem;">Grand Total</td>
                    <td class="text-end p-1 fw-bold border-dark border-1" style="font-size: 1rem;">{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end p-1 fw-bold border-dark border-1" style="font-size: 0.9rem;">Paid</td>
                    <td class="text-end p-1 fw-bold border-dark border-1" style="font-size: 0.9rem;">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end p-1 fw-bold border-dark border-1" style="font-size: 1.1rem; border-bottom: 2px solid #000;">Balance</td>
                    <td class="text-end p-1 fw-bold border-dark border-1" style="font-size: 1.1rem; border-bottom: 2px solid #000;">{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="row mt-5 pt-5">
            <div class="col-6 text-center">
                <div style="border-top: 2px solid #000; width: 70%; margin: 0 auto;"></div>
                <p class="fw-bold mt-1">Authorized By</p>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 2px solid #000; width: 70%; margin: 0 auto;"></div>
                <p class="fw-bold mt-1">Received By / Entered By</p>
            </div>
        </div>

        <div class="mt-5 pt-3 border-top border-dark text-muted small text-end" style="font-size: 10px;">
           Generated on {{ date('Y-m-d H:i:s') }}
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
         <form action="{{ route('purchases.add-payment', $purchase->id) }}" method="POST">
             @csrf
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Add Purchase Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Amount to Pay</label>
                        <div class="input-group input-group-lg">
                             <span class="input-group-text bg-light border-0">Rs.</span>
                             <input type="number" step="0.01" class="form-control bg-light border-0 fw-bold" name="amount" required max="{{ $purchase->total_amount - $purchase->paid_amount }}" value="{{ $purchase->total_amount - $purchase->paid_amount }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Payment Method</label>
                        <select class="form-select bg-light border-0" name="payment_method" id="modalPaymentMethod" required onchange="toggleModalFields()">
                             <option value="cash">Cash</option>
                             <option value="cheque">Cheque</option>
                             <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <!-- Cheque Details (Hidden by default) -->
                    <div id="modalChequeFields" class="d-none border rounded p-3 bg-light mb-3">
                        <h6 class="small fw-bold mb-3 d-flex align-items-center"><i class="fa-solid fa-money-check me-2 text-primary"></i>Out-Cheque Details</h6>
                        <div class="mb-2">
                            <label class="small text-muted mb-1">Cheque Number (6 Digits)</label>
                            <input type="text" class="form-control form-control-sm border-0 shadow-none" name="cheque_number" maxlength="6" minlength="6" placeholder="######">
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted mb-1">Our Bank</label>
                            <select class="form-select form-select-sm border-0 shadow-none" name="bank_id">
                                <option value="">Select Bank</option>
                                @foreach($banks ?? [] as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted mb-1">Cheque Date</label>
                            <input type="date" class="form-control form-control-sm border-0 shadow-none" name="cheque_date">
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted mb-1">Payee Name (Supplier)</label>
                            <input type="text" class="form-control form-control-sm border-0 shadow-none fw-bold" name="payee_name" value="{{ $purchase->supplier->full_name }}">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Notes</label>
                        <textarea class="form-control bg-light border-0" name="notes" rows="2" placeholder="Any additional info..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-toggle="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Record Payment</button>
                </div>
            </div>
         </form>
    </div>
</div>

<script>
    function toggleModalFields() {
        const method = document.getElementById('modalPaymentMethod').value;
        const chequeDiv = document.getElementById('modalChequeFields');
        
        if (method === 'cheque') {
            chequeDiv.classList.remove('d-none');
            // Add required attributes for validation
            chequeDiv.querySelectorAll('input, select').forEach(el => {
                if(!el.placeholder || el.placeholder != "Any additional info...") el.required = true;
            });
        } else {
            chequeDiv.classList.add('d-none');
            // Remove required attributes
            chequeDiv.querySelectorAll('input, select').forEach(el => el.required = false);
        }
    }
</script>

<style>
    @media print {
        @page {
            margin: 1cm;
            size: auto;
        }
        body {
            background: white !important;
            visibility: hidden;
        }
        .invoice-wrapper {
            visibility: visible;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            display: block !important;
        }
        .invoice-wrapper * {
            visibility: visible;
        }
        .d-print-none, nav, aside, header, footer, .btn, .container-fluid {
            display: none !important;
        }
        .invoice-container {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
        }
        .table-bordered border-dark td, .table-bordered border-dark th {
            border-color: #000 !important;
        }
    }
</style>
@endsection
