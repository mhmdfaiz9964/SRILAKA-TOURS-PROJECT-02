@extends('layouts.app')

@section('content')
<!-- Screen Layout: Visible only on screen -->
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

    <!-- Payment Modal Trigger -->
    <div class="mb-3 text-end">
        @if($purchase->status !== 'paid')
        <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="fa-solid fa-money-bill me-2"></i> Add Payment
        </button>
        @else
        <button class="btn btn-success fw-bold" disabled>
            <i class="fa-solid fa-check-double me-2"></i> Fully Paid
        </button>
        @endif
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-5">
                        <div>
                            <h5 class="fw-bold text-primary">{{ $globalSettings['company_name'] ?? config('app.name') }}</h5>
                            <p class="small text-muted mb-0">{{ $globalSettings['company_address'] ?? 'Address N/A' }}</p>
                            <p class="small text-muted">Phone: {{ $globalSettings['company_phone'] ?? 'N/A' }}</p>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-bold text-muted text-uppercase small">Supplier:</h6>
                            <h5 class="fw-bold mb-1">{{ $purchase->supplier->full_name }}</h5>
                            <p class="small text-muted mb-0">{{ $purchase->supplier->company_name }}</p>
                            <p class="small text-muted">{{ $purchase->supplier->contact_number }}</p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 15%;">Item Code</th>
                                    <th style="width: 40%;">Description</th>
                                    <th class="text-center" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 15%;">Cost Price</th>
                                    <th class="text-end pe-3" style="width: 20%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td class="ps-3 fw-bold small text-primary">{{ $item->product->code }}</td>
                                    <td>
                                        <div class="fw-bold small">{{ $item->product->name }}</div>
                                        @if($item->description)
                                        <div class="text-muted small fst-italic" style="font-size: 0.75rem;">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center small">{{ $item->quantity }}</td>
                                    <td class="text-end small">Rs. {{ number_format($item->cost_price, 2) }}</td>
                                    <td class="text-end pe-3 fw-bold small">Rs. {{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

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
                    <th style="width: 110px;">Item Code</th>
                    <th style="width: 70px;">Qty</th>
                    <th style="width: 320px;">Description</th>
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
                {{-- No more empty rows to avoid clutter --}}
            </tbody>
            <tfoot>
                {{-- Financial Summary Grid --}}
                <tr>
                    <td colspan="3" class="p-0 align-top" style="border-bottom: none;">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="ps-2 fw-bold text-decoration-underline" style="font-size: 0.9rem;">Investors</td>
                                <td></td>
                            </tr>
                            @foreach($purchase->investors as $investor)
                            <tr>
                                <td class="ps-2 py-0" style="font-size: 0.85rem;">{{ $investor->investor_name }}:</td>
                                <td class="text-end pe-2 py-0 fw-bold" style="font-size: 0.85rem;">{{ number_format($investor->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                    <td colspan="2" class="p-0">
                        <table class="table table-bordered border-dark border-0 mb-0 w-100" style="border-top: none !important;">
                            <tr>
                                <td class="text-end p-1 border-top-0" style="font-size: 0.85rem; width: 100px;">Sub Total Item</td>
                                <td class="text-end p-1 fw-bold border-top-0" style="font-size: 0.85rem; width: 121px;">{{ number_format($purchase->items->sum('total_price'), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Transport</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->transport_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Broker</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->broker_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Loading</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->loading_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Unloading</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->unloading_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Labour Charges</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->labour_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Air Ticket</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->air_ticket_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end p-1" style="font-size: 0.85rem;">Other Expenses</td>
                                <td class="text-end p-1" style="font-size: 0.85rem;">{{ number_format($purchase->other_expenses, 2) }}</td>
                            </tr>
                            <tr class="bg-light">
                                <td class="text-end p-1 fw-bold" style="font-size: 1rem;">Grand Total</td>
                                <td class="text-end p-1 fw-bold" style="font-size: 1rem;">{{ number_format($purchase->total_amount, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end fw-bold p-1">Paid</td>
                    <td colspan="2" class="text-end fw-bold p-1">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end fw-bold p-1 h5 mb-0">Balance</td>
                    <td colspan="2" class="text-end fw-bold p-1 h5 mb-0">{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
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
