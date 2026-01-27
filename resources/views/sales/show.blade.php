@extends('layouts.app')

@section('content')
<!-- Screen Layout: Visible only on screen -->
<div class="container-fluid d-print-none">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-gray-800 mb-0">Invoice #{{ $sale->invoice_number }}</h4>
            <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print Invoice
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Payment Modal Trigger -->
    <div class="mb-3 text-end">
        @if($sale->status !== 'paid')
        <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="fa-solid fa-money-bill me-2"></i> Add Payment
        </button>
        @else
        <button class="btn btn-success fw-bold" disabled>
            <i class="fa-solid fa-check-double me-2"></i> Fully Paid
        </button>
        @endif
    </div>

    <!-- Standard Card Layout for Screen -->
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
                                    <th style="width: 45%;">Product Description</th>
                                    <th class="text-center" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 15%;">Unit Price</th>
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
                                        @if($item->description)
                                        <div class="text-muted small fst-italic" style="font-size: 0.75rem;">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }} {{ $item->product->units }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end pe-3 fw-bold">Rs. {{ number_format($item->total_price, 2) }}</td>
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
                                    <td class="text-end fw-bold">Rs. {{ number_format($sale->total_amount - ($sale->transport_cost ?? 0) + $sale->discount_amount, 2) }}</td>
                                </tr>
                                 @if($sale->transport_cost > 0)
                                <tr>
                                    <td class="text-end fw-bold text-muted">Transport:</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($sale->transport_cost, 2) }}</td>
                                </tr>
                                @endif
                                @if($sale->discount_amount > 0)
                                <tr>
                                    <td class="text-end fw-bold text-muted">Discount:</td>
                                    <td class="text-end text-danger">- Rs. {{ number_format($sale->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="text-end h5 fw-bold">Total:</td>
                                    <td class="text-end h5 fw-bold text-primary">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-muted">Paid:</td>
                                    <td class="text-end text-success">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-muted">Balance Due:</td>
                                    <td class="text-end text-danger">Rs. {{ number_format(max(0, $sale->total_amount - $sale->paid_amount), 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Payment History Section -->
                    <div class="mt-4 border-top pt-4">
                        <h6 class="fw-bold mb-3">Payment History</h6>
                        @if($sale->payments->count() > 0)
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
                                        @foreach($sale->payments as $payment)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                                            <td class="text-uppercase small fw-bold">
                                                {{ $payment->payment_method ?? 'N/A' }}
                                                @if($payment->payment_method == 'cheque' && $payment->cheque)
                                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;">
                                                        #{{ $payment->cheque->cheque_number }} - {{ $payment->cheque->bank->name ?? 'Bank' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-success">{{ number_format($payment->amount, 2) }}</td>
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
                    
                    <h6 class="fw-bold mb-3 mt-4">Salesman</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-primary text-white p-2">
                                {{ strtoupper(substr($sale->salesman->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 fw-bold small">{{ $sale->salesman->name ?? 'Unassigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Layout: Hidden on screen, Visible on Print -->
<div class="invoice-wrapper d-none d-print-block">
    <div class="invoice-container bg-white mx-auto" style="width: 100%; max-width: 800px; color: black; font-family: 'Times New Roman', Times, serif;">
        
        <!-- Header -->
        <div class="border-bottom border-2 border-dark pb-2 mb-2">
            <div class="row align-items-center">
                <div class="col-4">
                     <!-- Logo Circle -->
                    <div style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        @if(!empty($globalSettings['company_logo']))
                            <img src="{{ asset($globalSettings['company_logo']) }}" alt="Logo" style="max-width: 100%; max-height: 100%;">
                        @else
                            <div style="border: 2px solid #000; width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                            LOGO
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-8 text-end">
                    <h2 class="fw-bold text-uppercase mb-1" style="color: #000080;">{{ $globalSettings['company_name'] ?? config('app.name') }}</h2>
                    <p class="mb-0 small fw-bold" style="white-space: pre-line;">{{ $globalSettings['company_address'] ?? '' }}</p>
                    <p class="mb-0 small fw-bold">Tel: {{ $globalSettings['company_phone'] ?? '' }}</p>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-6 text-start">
                   <span class="fw-bold">Online No: {{ $sale->invoice_number }}</span>
                </div>
                <div class="col-6 text-end">
                    <span class="fw-bold">Date : {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center mb-3">
            <h3 class="fw-bold text-decoration-underline" style="color: #000080;">Invoice</h3>
        </div>

        <!-- Customer Details -->
        <div class="mb-3">
            <div class="d-flex mb-1" style="border-bottom: 1px dotted #999;">
                <span class="fw-bold" style="width: 80px;">Name:</span>
                <span class="flex-grow-1 ps-2">{{ $sale->customer->full_name }}</span>
            </div>
            <div class="d-flex mb-1" style="border-bottom: 1px dotted #999;">
                <span class="fw-bold" style="width: 80px;">Address:</span>
                <span class="flex-grow-1 ps-2">{{ $sale->customer->address ?? '' }}</span>
                <span class="fw-bold ms-3">Tel:</span>
                <span class="ps-2" style="width: 150px;">{{ $sale->customer->mobile_number }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-2">
            <table class="table table-bordered border-dark mb-0" style="font-size: 0.9rem;">
                <thead>
                    <tr class="text-center bg-light">
                        <th style="width: 15%; padding: 4px;">Role/ pieces</th>
                        <th style="width: 15%; padding: 4px;">Qty</th>
                        <th style="width: 40%; padding: 4px;">Description</th>
                        <th style="width: 15%; padding: 4px;">Rate</th>
                        <th style="width: 15%; padding: 4px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="text-center p-1">{{ $item->product->code }}</td>
                        <td class="text-center p-1">{{ $item->quantity }} {{ $item->product->units }}</td>
                        <td class="p-1">
                            {{ $item->product->name }}
                            @if($item->description) <br><small class="text-muted fst-italic">({{ $item->description }})</small> @endif
                        </td>
                        <td class="text-end p-1">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold p-1">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                    @for($i = 0; $i < max(0, 8 - count($sale->items)); $i++)
                    <tr style="height: 25px;">
                        <td class="p-1">&nbsp;</td>
                        <td class="p-1"></td>
                        <td class="p-1"></td>
                        <td class="p-1"></td>
                        <td class="p-1"></td>
                    </tr>
                    @endfor
                </tbody>
                <tfoot>
                     <tr>
                        <td colspan="3" rowspan="4" class="align-top p-2" style="border-right: 1px solid #000;">
                            <div class="d-flex gap-3 mb-2 small fw-bold">
                                <div class="d-flex align-items-center">
                                    <div style="width:15px; height:15px; border:1px solid #000; position: relative;" class="me-1">
                                        @if($sale->payment_method == 'cash') <i class="fa-solid fa-check small position-absolute top-50 start-50 translate-middle"></i> @endif
                                    </div> Cash
                                </div>
                                <div class="d-flex align-items-center">
                                    <div style="width:15px; height:15px; border:1px solid #000; position: relative;" class="me-1">
                                        @if($sale->payment_method == 'cheque') <i class="fa-solid fa-check small position-absolute top-50 start-50 translate-middle"></i> @endif
                                    </div> Cheque
                                </div>
                                <div class="d-flex align-items-center">
                                    <div style="width:15px; height:15px; border:1px solid #000; position: relative;" class="me-1">
                                        @if($sale->payment_method == 'bank_transfer') <i class="fa-solid fa-check small position-absolute top-50 start-50 translate-middle"></i> @endif
                                    </div> Bank
                                </div>
                                <div class="d-flex align-items-center">
                                    <div style="width:15px; height:15px; border:1px solid #000; position: relative;" class="me-1">
                                        @if($sale->payment_method == 'credit' || $sale->payment_method == 'account') <i class="fa-solid fa-check small position-absolute top-50 start-50 translate-middle"></i> @endif
                                    </div> A/C
                                </div>
                            </div>
                        </td>
                        <td class="text-end fw-bold p-1">Sub Total</td>
                        <td class="text-end fw-bold p-1">{{ number_format($sale->total_amount - ($sale->transport_cost ?? 0) + $sale->discount_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end fw-bold p-1">Transport</td>
                        <td class="text-end fw-bold p-1">{{ number_format($sale->transport_cost ?? 0, 2) }}</td>
                    </tr>
                     <tr>
                        <td class="text-end fw-bold p-1">Discount</td>
                        <td class="text-end fw-bold p-1">-{{ number_format($sale->discount_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr style="background: #eee;">
                        <td class="text-end fw-bold p-1 h6 mb-0">Total</td>
                        <td class="text-end fw-bold p-1 h6 mb-0">{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border-0"></td>
                        <td class="text-end fw-bold p-1">Advance (Paid)</td>
                        <td class="text-end fw-bold p-1">{{ number_format($sale->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border-0"></td>
                        <td class="text-end fw-bold p-1 h5 mb-0">Balance</td>
                        <td class="text-end fw-bold p-1 h5 mb-0">{{ number_format($sale->total_amount - $sale->paid_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signatures -->
        <div class="row mt-5 pt-4">
            <div class="col-4 text-center">
                <div style="border-top: 1px dotted #000; width: 80%; margin: 0 auto;"></div>
                <p class="small fw-bold mt-1">Received by</p>
            </div>
            <div class="col-4 text-center">
                <div style="border-top: 1px dotted #000; width: 80%; margin: 0 auto;"></div>
                <p class="small fw-bold mt-1">Salesman: {{ $sale->salesman->name ?? '' }}</p>
            </div>
            <div class="col-4 text-center">
                <div style="border-top: 1px dotted #000; width: 80%; margin: 0 auto;"></div>
                <p class="small fw-bold mt-1">Cashier: {{ auth()->user()->name ?? 'System' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
         <form action="{{ route('sales.add-payment', $sale->id) }}" method="POST"> <!-- I need to add this route -->
             @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Amount</label>
                        <div class="input-group">
                             <span class="input-group-text">Rs.</span>
                             <input type="number" step="0.01" class="form-control" name="amount" required max="{{ $sale->total_amount - $sale->paid_amount }}" value="{{ $sale->total_amount - $sale->paid_amount }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Payment Method</label>
                        <select class="form-select" name="payment_method" id="modalPaymentMethod" required onchange="toggleModalFields()">
                             <option value="cash">Cash</option>
                             <option value="cheque">Cheque</option>
                             <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <!-- Cheque Details (Hidden by default) -->
                    <div id="modalChequeFields" class="d-none border rounded p-3 bg-light mb-3">
                        <h6 class="small fw-bold mb-2">Cheque Information</h6>
                        <div class="mb-2">
                            <label class="small text-muted">Cheque Number (6 Digits)</label>
                            <input type="text" class="form-control form-control-sm" name="cheque_number" maxlength="6" minlength="6" placeholder="######">
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Bank</label>
                            <select class="form-select form-select-sm" name="bank_id">
                                <option value="">Select Bank</option>
                                @foreach($banks ?? [] as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Cheque Date</label>
                            <input type="date" class="form-control form-control-sm" name="cheque_date">
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted">Payer Name</label>
                            <input type="text" class="form-control form-control-sm" name="payer_name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Save Payment</button>
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
            chequeDiv.querySelectorAll('input, select').forEach(el => el.required = true);
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
            margin: 0;
            size: auto;
        }
        body {
            background: white;
            visibility: hidden; /* Hide everything by default */
        }
        .invoice-wrapper {
            visibility: visible; /* Show only the invoice */
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            display: block !important;
        }
        .invoice-wrapper * {
            visibility: visible; /* Ensure children are visible */
        }
        
        .container-fluid, nav, footer, .btn, .d-print-none, header, aside {
            display: none !important;
        }
        
        .invoice-container {
            width: 100% !important;
            max-width: 100% !important;
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection
