@extends('layouts.app')

@section('content')
<!-- Screen Layout: Visible only on screen -->
<div class="container-fluid d-print-none">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Purchase Details</h1>
    </div>

    <div class="row">
        <!-- Sidebar Info -->
        <div class="col-lg-3">
             <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Purchase Summary</h6>
                    <div class="mb-3 pb-3 border-bottom text-center">
                        <div class="avatar-circle mx-auto bg-primary-subtle text-primary d-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.2rem;">
                            {{ substr($purchase->supplier->full_name ?? 'P', 0, 1) }}
                        </div>
                        <h5 class="fw-bold mb-0 text-truncate px-2">{{ $purchase->supplier->full_name }}</h5>
                        <p class="text-muted small mb-0">{{ $purchase->supplier->company_name ?? 'Supplier' }}</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Reference</small>
                        <div class="fw-bold fs-6">GRN: {{ $purchase->grn_number ?? 'N/A' }}</div>
                        <div class="small">Invoice: {{ $purchase->invoice_number ?? 'N/A' }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Date</small>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</div>
                    </div>

                    <div class="mb-4">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Status</small>
                        @php
                            $statusClass = [
                                'unpaid' => 'bg-danger-subtle text-danger',
                                'partial' => 'bg-warning-subtle text-warning',
                                'paid' => 'bg-success-subtle text-success'
                            ][$purchase->status] ?? 'bg-secondary-subtle text-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }} rounded-pill border-0 px-3 py-2 fw-bold text-uppercase" style="font-size: 0.65rem;">{{ $purchase->status }}</span>
                    </div>

                    <div class="d-grid gap-2 pt-3 border-top">
                        <button class="btn btn-primary rounded-pill shadow-sm py-2" onclick="window.print()">
                            <i class="fa-solid fa-print me-2"></i> Print Note
                        </button>
                        <button class="btn btn-success rounded-pill shadow-sm py-2" onclick="shareViaWhatsApp()">
                            <i class="fa-brands fa-whatsapp me-2"></i> Share via WhatsApp
                        </button>
                        @if($purchase->status !== 'paid')
                        <button class="btn btn-warning rounded-pill shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fa-solid fa-plus me-2"></i> Add Payment
                        </button>
                        @endif
                        <a href="{{ route('purchases.index') }}" class="btn btn-light rounded-pill border py-2">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area with Tabs -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-pill" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active rounded-pill fw-bold small" data-bs-toggle="tab" href="#itemsTab">
                                <i class="fa-solid fa-box me-1"></i> Purchased Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill fw-bold small" data-bs-toggle="tab" href="#paymentsTab">
                                <i class="fa-solid fa-money-bill-transfer me-1"></i> Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill fw-bold small" data-bs-toggle="tab" href="#investorsTab">
                                <i class="fa-solid fa-users-gear me-1"></i> Investors
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- Items Tab -->
                        <div class="tab-pane fade show active" id="itemsTab">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3">Itemized Breakdown</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border-bottom mb-4">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-3 py-3 text-muted small text-uppercase">Product</th>
                                            <th class="text-center py-3 text-muted small text-uppercase">Qty</th>
                                            <th class="text-end py-3 text-muted small text-uppercase">Unit Cost</th>
                                            <th class="text-end pe-3 py-3 text-muted small text-uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->items as $item)
                                        <tr>
                                            <td class="ps-3 py-3">
                                                <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                                <div class="text-muted small">Code: {{ $item->product->code }}</div>
                                                @if($item->description)
                                                    <div class="text-muted small fst-italic">({{ $item->description }})</div>
                                                @endif
                                            </td>
                                            <td class="text-center py-3">{{ $item->quantity }}</td>
                                            <td class="text-end py-3">{{ number_format($item->cost_price, 2) }}</td>
                                            <td class="text-end pe-3 py-3 fw-bold">{{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row align-items-start">
                                <div class="col-md-7">
                                    @if($purchase->notes)
                                        <h6 class="fw-bold text-muted text-uppercase small mb-2">Internal Notes</h6>
                                        <p class="small text-muted border-start border-3 ps-3 py-1">{{ $purchase->notes }}</p>
                                    @endif
                                </div>
                                <div class="col-md-5">
                                    <div class="bg-light p-3 rounded-4 shadow-sm">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Item Sub Total:</span>
                                            <span class="fw-bold small">{{ number_format($purchase->items->sum('total_price'), 2) }}</span>
                                        </div>
                                        @php
                                            $costs = [
                                                ['label' => 'Transport', 'val' => $purchase->transport_cost],
                                                ['label' => 'Broker', 'val' => $purchase->broker_cost],
                                                ['label' => 'Loading', 'val' => $purchase->loading_cost],
                                                ['label' => 'Unloading', 'val' => $purchase->unloading_cost],
                                                ['label' => 'Labour', 'val' => $purchase->labour_cost],
                                                ['label' => 'Air Ticket', 'val' => $purchase->air_ticket_cost],
                                                ['label' => 'Other', 'val' => $purchase->other_expenses],
                                            ];
                                        @endphp
                                        @foreach($costs as $cost)
                                            @if($cost['val'] > 0)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">{{ $cost['label'] }}:</span>
                                                <span class="fw-bold small">{{ number_format($cost['val'], 2) }}</span>
                                            </div>
                                            @endif
                                        @endforeach
                                        <div class="d-flex justify-content-between mt-3 pt-3 border-top border-dark border-opacity-10">
                                            <span class="fw-bold h6 mb-0">Grand Total:</span>
                                            <span class="fw-bold h6 mb-0 text-primary">Rs. {{ number_format($purchase->total_amount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1 text-success">
                                            <span class="small fw-bold">Total Paid:</span>
                                            <span class="small fw-bold">Rs. {{ number_format($purchase->paid_amount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1 text-danger">
                                            <span class="small fw-bold">Balance:</span>
                                            <span class="small fw-bold">Rs. {{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="paymentsTab">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3">Settlement History</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 py-3 text-muted small text-uppercase">Date</th>
                                            <th class="py-3 text-muted small text-uppercase">Method</th>
                                            <th class="text-end py-3 text-muted small text-uppercase">Amount</th>
                                            <th class="pe-3 py-3 text-muted small text-uppercase">Ref / Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchase->payments as $payment)
                                        <tr>
                                            <td class="ps-3 py-3 small text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 text-uppercase" style="font-size: 0.65rem;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-danger">{{ number_format($payment->amount, 2) }}</td>
                                            <td class="pe-3 small">
                                                @if($payment->payment_method == 'cheque')
                                                    <span class="text-dark fw-semibold">#{{ $payment->payment_cheque_number }}</span>
                                                @elseif($payment->payment_method == 'bank_transfer')
                                                    <span class="text-dark fw-semibold">{{ $payment->reference_number ?? 'Bank Transfer' }}</span>
                                                @endif
                                                <div class="text-muted text-truncate" style="max-width: 300px;">{{ $payment->notes }}</div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted small py-5 fst-italic">No payments recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Investors Tab -->
                        <div class="tab-pane fade" id="investorsTab">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3">Investment Allocation</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 py-3 text-muted small text-uppercase">Investor</th>
                                            <th class="text-end pe-3 py-3 text-muted small text-uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchase->investors as $investor)
                                        <tr>
                                            <td class="ps-3 py-3 fw-bold text-dark">{{ $investor->investor_name }}</td>
                                            <td class="text-end pe-3 py-3 fw-bold text-primary">Rs. {{ number_format($investor->amount, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center text-muted small py-5">No investor allocation found for this purchase.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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
                        ['label' => 'Sub Total Item', 'val' => $purchase->items->sum('total_price'), 'bold' => true],
                        ['label' => 'Transport', 'val' => $purchase->transport_cost, 'bold' => false],
                        ['label' => 'Broker', 'val' => $purchase->broker_cost, 'bold' => false],
                        ['label' => 'Loading', 'val' => $purchase->loading_cost, 'bold' => false],
                        ['label' => 'Unloading', 'val' => $purchase->unloading_cost, 'bold' => false],
                        ['label' => 'Labour Charges', 'val' => $purchase->labour_cost, 'bold' => false],
                        ['label' => 'Air Ticket', 'val' => $purchase->air_ticket_cost, 'bold' => false],
                        ['label' => 'Other Expenses', 'val' => $purchase->other_expenses, 'bold' => false],
                    ];
                @endphp

                @foreach($additionalCosts as $cost)
                <tr>
                    <td colspan="3" class="border-0"></td>
                    <td class="text-end p-1 border-dark border-1" style="font-size: 0.9rem;">{{ $cost['label'] }}</td>
                    <td class="text-end p-1 border-dark border-1 {{ $cost['bold'] ? 'fw-bold' : '' }}" style="font-size: 0.9rem;">{{ number_format($cost['val'], 2) }}</td>
                </tr>
                @endforeach

                <tr style="background: #f1f3f4 !important; -webkit-print-color-adjust: exact;">
                    <td colspan="3" rowspan="3" class="align-top p-2 border-dark border-1">
                        <h6 class="fw-bold text-decoration-underline small mb-1">Investors & Notes</h6>
                        <table class="table table-sm table-borderless mb-0">
                            @foreach($purchase->investors as $investor)
                            <tr>
                                <td class="p-0 small" style="font-size: 0.8rem;">{{ $investor->investor_name }}:</td>
                                <td class="p-0 small fw-bold text-end" style="font-size: 0.8rem;">{{ number_format($investor->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </table>
                        <div class="mt-2 small text-muted">{{ $purchase->notes }}</div>
                    </td>
                    <td class="text-end p-1 fw-bold border-dark border-1 h5 mb-0" style="background: #eee;">Grand Total</td>
                    <td class="text-end p-1 fw-bold border-dark border-1 h5 mb-0" style="background: #eee;">{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end p-1 fw-bold border-dark border-1">Paid</td>
                    <td class="text-end p-1 fw-bold border-dark border-1">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end p-1 fw-bold border-dark border-1 border-bottom-2 h4 mb-0">Balance</td>
                    <td class="text-end p-1 fw-bold border-dark border-1 border-bottom-2 h4 mb-0">{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-5 pt-4 text-center">
            <div class="col-6">
                <div class="d-inline-block border-top border-dark px-5 pt-2" style="border-top-style: dotted !important; width: 80%;">
                    <strong class="small">Authorized By</strong>
                </div>
            </div>
            <div class="col-6">
                <div class="d-inline-block border-top border-dark px-5 pt-2" style="border-top-style: dotted !important; width: 80%;">
                    <strong class="small">Received By / Entered By</strong>
                </div>
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
                    <h5 class="modal-title fw-bold">Settlement Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 rounded-3 border-0 small mb-4">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Recording payment for <strong>{{ $purchase->supplier->full_name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Amount to Pay</label>
                        <div class="input-group input-group-lg">
                             <span class="input-group-text bg-light border-0">Rs.</span>
                             <input type="number" step="0.01" class="form-control bg-light border-0 fw-bold" name="amount" required max="{{ $purchase->total_amount - $purchase->paid_amount }}" value="{{ $purchase->total_amount - $purchase->paid_amount }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Payment Method</label>
                        <div class="d-flex gap-2">
                             @foreach(['cash' => 'Cash', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer'] as $val => $label)
                                <input type="radio" class="btn-check" name="payment_method" id="method_{{ $val }}" value="{{ $val }}" {{ $val == 'cash' ? 'checked' : '' }} onchange="toggleModalFields()">
                                <label class="btn btn-outline-secondary w-100 rounded-pill small" for="method_{{ $val }}">{{ $label }}</label>
                             @endforeach
                        </div>
                    </div>
                    
                    <!-- Cheque Details -->
                    <div id="modalChequeFields" class="d-none border rounded-4 p-3 bg-light mb-4 shadow-sm border-primary-subtle">
                        <h6 class="small fw-bold mb-3 text-primary d-flex align-items-center"><i class="fa-solid fa-money-check me-2"></i>Outgoing Cheque Details</h6>
                        <div class="row g-2">
                            <div class="col-6 mb-2">
                                <label class="small text-muted mb-1">Cheque Number</label>
                                <input type="text" class="form-control form-control-sm border-white shadow-sm" name="cheque_number" maxlength="6" minlength="6" placeholder="######">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="small text-muted mb-1">Cheque Date</label>
                                <input type="date" class="form-control form-control-sm border-white shadow-sm" name="cheque_date">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="small text-muted mb-1">Drawn From (Our Bank)</label>
                                <select class="form-select form-select-sm border-white shadow-sm" name="bank_id">
                                    <option value="">Select Bank</option>
                                    @foreach($banks ?? [] as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="small text-muted mb-1">Payee Name</label>
                                <input type="text" class="form-control form-control-sm border-white shadow-sm fw-bold" name="payee_name" value="{{ $purchase->supplier->full_name }}">
                            </div>
                        </div>
                    </div>

                    <!-- Bank Transfer Details -->
                    <div id="modalBankTransferFields" class="d-none border rounded-4 p-3 bg-light mb-4 shadow-sm border-info-subtle">
                        <h6 class="small fw-bold mb-3 text-info d-flex align-items-center"><i class="fa-solid fa-building-columns me-2"></i>Transfer Details</h6>
                        <div class="row g-2">
                            <div class="col-6 mb-2">
                                <label class="small text-muted mb-1">Transfer Ref #</label>
                                <input type="text" class="form-control form-control-sm border-white shadow-sm" name="reference_number" placeholder="Ref/TrX ID">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="small text-muted mb-1">Our Bank</label>
                                <select class="form-select form-select-sm border-white shadow-sm" name="transfer_bank_id">
                                    <option value="">Select Bank</option>
                                    @foreach($banks ?? [] as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Notes (Optional)</label>
                        <textarea class="form-control bg-light border-0 rounded-3" name="notes" rows="2" placeholder="Any additional info..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow">Record Payment</button>
                </div>
            </div>
         </form>
    </div>
</div>

<script>
    function shareViaWhatsApp() {
        const grnNumber = "{{ $purchase->grn_number ?? 'N/A' }}";
        const supplierName = "{{ $purchase->supplier->full_name }}";
        const totalAmount = "{{ number_format($purchase->total_amount, 2) }}";
        const paidAmount = "{{ number_format($purchase->paid_amount, 2) }}";
        const balance = "{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}";
        const companyName = "{{ $globalSettings['company_name'] ?? config('app.name') }}";
        const purchaseUrl = window.location.href;
        
        // Create message
        const message = `*${companyName}*\n\n` +
                       `Purchase Order\n` +
                       `GRN: *${grnNumber}*\n` +
                       `Supplier: ${supplierName}\n\n` +
                       `Total Amount: Rs. ${totalAmount}\n` +
                       `Paid: Rs. ${paidAmount}\n` +
                       `Balance: Rs. ${balance}\n\n` +
                       `View Details: ${purchaseUrl}`;
        
        // Get supplier phone number (remove spaces and special characters)
        const supplierPhone = "{{ $purchase->supplier->contact_number }}".replace(/[^0-9]/g, '');
        
        // Create WhatsApp URL
        let whatsappUrl = `https://wa.me/${supplierPhone}?text=${encodeURIComponent(message)}`;
        
        // Open WhatsApp
        window.open(whatsappUrl, '_blank');
    }

    function toggleModalFields() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const chequeDiv = document.getElementById('modalChequeFields');
        const transferDiv = document.getElementById('modalBankTransferFields');
        
        chequeDiv.classList.add('d-none');
        transferDiv.classList.add('d-none');
        
        // Reset required fields
        document.querySelectorAll('#modalChequeFields input, #modalChequeFields select, #modalBankTransferFields input, #modalBankTransferFields select').forEach(el => el.required = false);

        if (method === 'cheque') {
            chequeDiv.classList.remove('d-none');
            chequeDiv.querySelectorAll('input, select').forEach(el => el.required = true);
        } else if (method === 'bank_transfer') {
            transferDiv.classList.remove('d-none');
            transferDiv.querySelectorAll('input, select').forEach(el => el.required = true);
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
