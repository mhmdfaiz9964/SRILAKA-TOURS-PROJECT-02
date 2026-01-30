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

        <!-- Tabs section -->
        <div class="col-md-9">
             <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <ul class="nav nav-pills gap-2 p-1 bg-light rounded-pill" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill fw-bold small px-4" data-bs-toggle="tab" data-bs-target="#supp-ledger" type="button">
                                <i class="fa-solid fa-list-ul me-1"></i> Ledger
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill fw-bold small px-4" data-bs-toggle="tab" data-bs-target="#supp-purchases" type="button">
                                <i class="fa-solid fa-cart-flatbed me-1"></i> Purchase History
                            </button>
                        </li>
                         <li class="nav-item">
                            <button class="nav-link rounded-pill fw-bold small px-4" data-bs-toggle="tab" data-bs-target="#supp-payments" type="button">
                                <i class="fa-solid fa-money-bill-wave me-1"></i> Payments
                            </button>
                        </li>
                    </ul>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary rounded-pill px-3 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-download me-1"></i> Export Ledger
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0">
                            <li><a class="dropdown-item py-2 small" href="{{ route('suppliers.ledger.export', ['supplier' => $supplier->id, 'format' => 'pdf']) }}"><i class="fa-solid fa-file-pdf me-2 text-danger"></i> Export PDF</a></li>
                            <li><a class="dropdown-item py-2 small" href="{{ route('suppliers.ledger.export', ['supplier' => $supplier->id, 'format' => 'excel']) }}"><i class="fa-solid fa-file-excel me-2 text-success"></i> Export Excel (CSV)</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- Ledger Tab -->
                        <div class="tab-pane fade show active" id="supp-ledger" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-uppercase text-muted small mb-0">Transaction History</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 py-2 text-muted small text-uppercase">Date</th>
                                            <th class="py-2 text-muted small text-uppercase">Description</th>
                                            <th class="text-end py-2 text-muted small text-uppercase">Debit (Purchase)</th>
                                            <th class="text-end py-2 text-muted small text-uppercase">Credit (Paid)</th>
                                            <th class="text-end pe-3 py-2 text-muted small text-uppercase">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                            $runningBalance = 0; 
                                        @endphp
                                        @forelse($ledger as $entry)
                                            @php 
                                                $debit = $entry['debit'] ?? 0;
                                                $credit = $entry['credit'] ?? 0;
                                                $runningBalance += ($debit - $credit);
                                            @endphp
                                            <tr>
                                                <td class="ps-4 small text-muted text-nowrap">{{ \Carbon\Carbon::parse($entry['date'])->format('d M, Y') }}</td>
                                                <td class="small">
                                                    @if(isset($entry['url']) && $entry['url'] != '#')
                                                        <a href="{{ $entry['url'] }}" class="fw-bold text-dark text-decoration-none">{{ $entry['description'] }}</a>
                                                    @else
                                                        <span class="fw-bold text-dark">{{ $entry['description'] }}</span>
                                                    @endif
                                                    
                                                    @if(isset($entry['payment_method']) && $entry['payment_method'] == 'cheque')
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            Cheque #: {{ $entry['cheque_number'] ?? '-' }} <span class="mx-1">|</span> Date: {{ $entry['cheque_date'] ?? '-' }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end small">
                                                    @if($debit > 0)
                                                        <span class="fw-semibold">{{ number_format($debit, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end small">
                                                    @if($credit > 0)
                                                        <span class="fw-semibold text-success">{{ number_format($credit, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4 small fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($runningBalance, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted small">No transactions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light border-top">
                                        @php
                                            $totalDebit = collect($ledger)->sum('debit');
                                            $totalCredit = collect($ledger)->sum('credit');
                                            $finalBalance = $totalDebit - $totalCredit;
                                        @endphp
                                        <tr>
                                            <td colspan="2" class="ps-4 py-3 fw-bold text-end text-uppercase">Total</td>
                                            <td class="text-end py-3 fw-bold">{{ number_format($totalDebit, 2) }}</td>
                                            <td class="text-end py-3 fw-bold text-success">{{ number_format($totalCredit, 2) }}</td>
                                            <td class="text-end pe-4 py-3 fw-bold {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($finalBalance, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Purchases Tab -->
                        <div class="tab-pane fade" id="supp-purchases" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 py-2 small fw-bold">Date</th>
                                            <th class="py-2 small fw-bold">Invoice #</th>
                                            <th class="py-2 small fw-bold">Total</th>
                                            <th class="py-2 small fw-bold">Status</th>
                                            <th class="text-end pe-3 py-2 small fw-bold">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplier->purchases as $purchase)
                                        <tr>
                                            <td class="ps-3 small">{{ $purchase->purchase_date }}</td>
                                            <td class="small fw-bold">{{ $purchase->invoice_number ?? '-' }}</td>
                                            <td class="small">{{ number_format($purchase->total_amount, 2) }}</td>
                                            <td class="small">
                                                <span class="badge bg-{{ $purchase->status == 'paid' ? 'success' : ($purchase->status == 'partial' ? 'warning' : 'danger') }}-subtle text-{{ $purchase->status == 'paid' ? 'success' : ($purchase->status == 'partial' ? 'warning' : 'danger') }} border-0">
                                                    {{ ucfirst($purchase->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
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

                         <!-- Payments Tab -->
                        <div class="tab-pane fade" id="supp-payments" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-uppercase text-muted small mb-0">Outgoing Payments</h6>
                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupPaymentModal">
                                    <i class="fa-solid fa-plus me-1"></i> New Payment
                                </button>
                            </div>

                             <div class="table-responsive">
                                 <table class="table table-hover align-middle mb-0">
                                     <thead class="bg-light">
                                         <tr>
                                             <th class="ps-4 py-3 text-muted small text-uppercase">Date</th>
                                             <th class="py-3 text-muted small text-uppercase">Method</th>
                                             <th class="text-end py-3 text-muted small text-uppercase">Amount</th>
                                             <th class="py-3 text-muted small text-uppercase">Reference / Details</th>
                                             <th class="pe-4 py-3 text-muted small text-uppercase">Notes</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @forelse($supplier->payments->sortByDesc('payment_date') as $payment)
                                         <tr>
                                             <td class="ps-4 small text-muted text-nowrap">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                                             <td>
                                                 @php
                                                     $icon = [
                                                         'cash' => 'fa-money-bill-1 text-success',
                                                         'cheque' => 'fa-money-check-dollar text-primary',
                                                         'bank_transfer' => 'fa-building-columns text-info'
                                                     ][$payment->payment_method] ?? 'fa-circle-dollar-to-slot';
                                                 @endphp
                                                 <i class="fa-solid {{ $icon }} me-1"></i>
                                                 <span class="small fw-bold text-uppercase">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                             </td>
                                             <td class="text-end fw-bold text-danger">{{ number_format($payment->amount, 2) }}</td>
                                             <td class="small">
                                                 @if($payment->payment_method == 'cheque')
                                                     <span class="text-dark fw-semibold">#{{ $payment->payment_cheque_number }}</span>
                                                     <div class="text-muted" style="font-size: 0.75rem;">{{ $payment->bank->name ?? '-' }} | {{ $payment->payment_cheque_date }}</div>
                                                 @elseif($payment->payment_method == 'bank_transfer')
                                                     <span class="text-dark fw-semibold">{{ $payment->reference_number ?? 'Transfer' }}</span>
                                                     <div class="text-muted" style="font-size: 0.75rem;">{{ $payment->bank->name ?? '-' }}</div>
                                                 @else
                                                     <span class="text-muted">Cash Payment</span>
                                                 @endif
                                             </td>
                                             <td class="pe-4 small text-muted">{{ $payment->notes ?? '-' }}</td>
                                         </tr>
                                         @empty
                                         <tr><td colspan="5" class="text-center text-muted small py-5">No payments recorded</td></tr>
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

<!-- Add Supplier Payment Modal -->
<div class="modal fade" id="addSupPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Add Outgoing Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payable_type" value="App\Models\Supplier">
                    <input type="hidden" name="payable_id" value="{{ $supplier->id }}">
                    <input type="hidden" name="type" value="out">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Payment Method</label>
                        <select class="form-select" name="payment_method" id="supPaymentMethod" onchange="toggleSupPaymentFields()">
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <!-- Cheque Details -->
                    <div id="supChequeFields" class="d-none border rounded p-3 bg-light mb-3">
                        <h6 class="small fw-bold mb-2">Cheque Information</h6>
                        <div class="mb-2">
                            <label class="small text-muted">Cheque Number</label>
                            <input type="text" class="form-control form-control-sm" name="payment_cheque_number">
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Bank</label>
                            @php $banks = \App\Models\Bank::all(); @endphp
                            <select class="form-select form-select-sm" name="bank_id">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="mb-2">
                            <label class="small text-muted">Cheque Date</label>
                            <input type="date" class="form-control form-control-sm" name="payment_cheque_date">
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted">Payee Name</label>
                            <input type="text" class="form-control form-control-sm" name="payee_name" value="{{ $supplier->full_name }}">
                        </div>
                    </div>

                     <!-- Bank Transfer Details -->
                    <div id="supBankFields" class="d-none border rounded p-3 bg-light mb-3">
                         <div class="mb-2">
                            <label class="small text-muted">Reference Number</label>
                            <input type="text" class="form-control form-control-sm" name="reference_number">
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Our Bank Account</label>
                             <select class="form-select form-select-sm" name="payment_bank_id">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                         <label class="form-label fw-bold small">Notes</label>
                         <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSupPaymentFields() {
        const method = document.getElementById('supPaymentMethod').value;
        const cheque = document.getElementById('supChequeFields');
        const bank = document.getElementById('supBankFields');
        
        cheque.classList.add('d-none');
        bank.classList.add('d-none');
        
        if(method === 'cheque') cheque.classList.remove('d-none');
        if(method === 'bank_transfer') bank.classList.remove('d-none');
    }
</script>
    </div>
</div>
@endsection
