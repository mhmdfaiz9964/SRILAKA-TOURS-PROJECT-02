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
             <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-pill" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active rounded-pill fw-bold small" data-bs-toggle="tab" href="#purchases">
                                <i class="fa-solid fa-cart-flatbed me-1"></i> Purchase History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill fw-bold small" data-bs-toggle="tab" href="#ledger">
                                <i class="fa-solid fa-list-ul me-1"></i> Ledger
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
                        <div class="tab-pane fade" id="ledger">
                             <div class="row g-0">
                                <!-- Left Side: Purchases (Inflow of Goods / Debit to Expense) -->
                                <div class="col-md-6 border-end">
                                    <div class="bg-light p-2 border-bottom text-center">
                                        <h6 class="fw-bold mb-0 text-dark small text-uppercase">Purchases</h6>
                                    </div>
                                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle mb-0 border-0">
                                            <thead class="text-muted bg-white position-sticky top-0">
                                                <tr>
                                                    <th class="ps-3 border-0 small">Date</th>
                                                    <th class="border-0 small">Description</th>
                                                    <th class="text-end pe-3 border-0 small">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $totalDebit = 0; @endphp
                                                @foreach($ledger->where('type', 'invoice') as $entry)
                                                    @php $totalDebit += $entry['debit']; @endphp
                                                    <tr>
                                                        <td class="ps-3 small border-0 text-muted">{{ \Carbon\Carbon::parse($entry['date'])->format('d M, Y') }}</td>
                                                        <td class="small border-0">
                                                            <a href="{{ $entry['url'] }}" class="text-decoration-none fw-bold text-dark">{{ $entry['description'] }}</a>
                                                        </td>
                                                        <td class="text-end pe-3 small border-0 fw-bold">{{ number_format($entry['debit'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Right Side: Payments (Outflow of Cash / Credit to Cash) -->
                                <div class="col-md-6">
                                    <div class="bg-light p-2 border-bottom text-center">
                                        <h6 class="fw-bold mb-0 text-dark small text-uppercase">Payments Made</h6>
                                    </div>
                                     <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle mb-0 border-0">
                                            <thead class="text-muted bg-white position-sticky top-0">
                                                <tr>
                                                    <th class="ps-3 border-0 small">Date</th>
                                                    <th class="border-0 small">Description</th>
                                                    <th class="text-end pe-3 border-0 small">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $totalCredit = 0; @endphp
                                                @foreach($ledger->where('type', 'payment') as $entry)
                                                    @php $totalCredit += $entry['credit']; @endphp
                                                    <tr>
                                                        <td class="ps-3 small border-0 text-muted">{{ \Carbon\Carbon::parse($entry['date'])->format('d M, Y') }}</td>
                                                        <td class="small border-0">
                                                            {!! $entry['description'] !!}
                                                        </td>
                                                        <td class="text-end pe-3 small border-0 fw-bold text-success">{{ number_format($entry['credit'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                             </div>

                             <!-- Totals Footer -->
                             <div class="bg-light p-3 border-top mt-0">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <small class="text-muted text-uppercase fw-bold d-block">Total Purchased</small>
                                        <span class="fs-5 fw-bold">{{ number_format($totalDebit, 2) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                         <small class="text-muted text-uppercase fw-bold d-block">Total Paid</small>
                                        <span class="fs-5 fw-bold text-success">{{ number_format($totalCredit, 2) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                         <small class="text-muted text-uppercase fw-bold d-block">Balance Payable</small>
                                        <span class="fs-5 fw-bold {{ ($totalDebit - $totalCredit) > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($totalDebit - $totalCredit, 2) }}
                                        </span>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Purchases Tab -->
                        <div class="tab-pane fade show active" id="purchases">
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
                        <div class="tab-pane fade" id="payments">
                            <div class="d-flex justify-content-between mb-3">
                                <h6 class="fw-bold text-gray-800">Payment History</h6>
                                <button type="button" class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addSupPaymentModal">
                                    <i class="fa-solid fa-plus me-1"></i> Add Payment
                                </button>
                            </div>

                             <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 small fw-bold">Date</th>
                                            <th class="small fw-bold">Method</th>
                                            <th class="small fw-bold">Amount</th>
                                            <th class="small fw-bold">Details</th>
                                            <th class="small fw-bold">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplier->payments as $payment)
                                        <tr>
                                            <td class="ps-3 small">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                                            <td class="small text-uppercase">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                            <td class="small fw-bold text-danger">-{{ number_format($payment->amount, 2) }}</td>
                                            <td class="small text-muted">
                                                @if($payment->payment_method == 'cheque')
                                                    Cheque #: {{ $payment->payment_cheque_number }} ({{ $payment->bank->name ?? '-' }})
                                                @elseif($payment->payment_method == 'bank_transfer')
                                                    Ref: {{ $payment->reference_number }} ({{ $payment->bank->name ?? '-' }})
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="small">{{ $payment->notes ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted small py-3">No payments recorded</td></tr>
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
