@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Cheque Details</h4>
                <p class="text-muted small mb-0">#{{ $cheque->cheque_number }} | {{ $cheque->bank->name }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('cheques.index') }}" class="btn btn-light btn-sm px-3 border-light">Back to List</a>
                <button class="btn btn-warning btn-sm text-white px-3 shadow-sm border-0 d-flex align-items-center gap-2" style="background: #f97316;" data-bs-toggle="modal" data-bs-target="#updateThirdPartyModal">
                    <i class="fa-solid fa-user-tag"></i> Update 3rd Party Status
                </button>
                @if($cheque->payment_status != 'paid')
                    <button class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fa-solid fa-plus"></i> Add Payment
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <!-- Cheque Summary Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="extra-small text-muted text-uppercase fw-bold mb-1">Cheque Amount</div>
                        <h2 class="fw-bold mb-0">LKR {{ number_format($cheque->amount, 2) }}</h2>
                        <span class="badge rounded-pill mt-2" style="background: {{ $cheque->payment_status == 'paid' ? '#ecfdf5' : '#fffbeb' }}; color: {{ $cheque->payment_status == 'paid' ? '#10b981' : '#f59e0b' }};">
                            {{ ucwords($cheque->payment_status) }}
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 bg-light rounded-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Paid Amount</span>
                                <span class="fw-bold text-success small">LKR {{ number_format($totalPaid, 2) }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($totalPaid / $cheque->amount) * 100 }}%"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="small fw-bold text-muted text-uppercase extra-small mb-1">Payer</div>
                                <div class="small fw-medium">{{ $cheque->payer_name }}</div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="small fw-bold text-muted text-uppercase extra-small mb-1">Payee</div>
                                <div class="small fw-medium">{{ $cheque->payee_name ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small fw-bold text-muted text-uppercase extra-small mb-1">Cheque Date</div>
                                <div class="small fw-medium">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d M, Y') }}</div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="small fw-bold text-muted text-uppercase extra-small mb-1">Bank</div>
                                <div class="small fw-medium">{{ $cheque->bank->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3rd Party Status Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 small text-uppercase text-muted">3rd Party Details</h6>
                    @if($cheque->payee_name)
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">3rd Part Name</span>
                                <span class="small fw-bold">{{ $cheque->payee_name }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Status</span>
                                <span class="badge rounded-pill" style="background: {{ $cheque->third_party_payment_status == 'paid' ? '#ecfdf5' : '#fff7ed' }}; color: {{ $cheque->third_party_payment_status == 'paid' ? '#10b981' : '#f97316' }};">
                                    {{ ucwords($cheque->third_party_payment_status) }}
                                </span>
                            </div>
                            @if($cheque->third_party_notes)
                                <div class="mt-2 p-2 bg-light rounded small italic">
                                    <i class="fa-solid fa-quote-left fa-xs text-muted me-1"></i> {{ $cheque->third_party_notes }}
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="small text-muted mb-0">No 3rd party assigned to this cheque.</p>
                    @endif
                </div>
            </div>

            @if($cheque->return_reason)
            <!-- Return Info Card -->
            <div class="card border-0 shadow-sm border-start border-4 border-danger" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-1 text-danger small text-uppercase">Return Information</h6>
                    <div class="small fw-bold mb-2">Reason: {{ $cheque->return_reason }}</div>
                    @if($cheque->notes)
                        <div class="extra-small text-muted">{{ $cheque->notes }}</div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-8">
            <!-- Payment History Card -->
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted fw-bold">Date</th>
                                    <th class="border-0 small text-uppercase text-muted fw-bold">Method</th>
                                    <th class="border-0 small text-uppercase text-muted fw-bold">Details</th>
                                    <th class="border-0 small text-uppercase text-muted fw-bold text-end">Amount</th>
                                    <th class="border-0 pe-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cheque->payments as $payment)
                                <tr>
                                    <td class="ps-4 py-3 small fw-medium">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                                    <td>
                                        <span class="small fw-medium">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($payment->payment_method == 'bank_transfer')
                                                <span class="extra-small text-muted">{{ $payment->bank->name ?? 'N/A' }} | #{{ $payment->reference_number }}</span>
                                            @elseif($payment->payment_method == 'cheque')
                                                <span class="extra-small text-muted">Chq: {{ $payment->payment_cheque_number }} | {{ $payment->payment_cheque_date }}</span>
                                            @else
                                                <span class="extra-small text-muted">Cash Payment</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-success small">LKR {{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-end pe-4">
                                        @if($payment->document)
                                            <a href="{{ asset('storage/' . $payment->document) }}" target="_blank" class="btn btn-sm btn-icon border-0 text-muted">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">No payments recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('cheques.add-payment', $cheque) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Amount</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light small text-muted">LKR</span>
                                <input type="number" step="0.01" name="amount" class="form-control border-light shadow-none" max="{{ $cheque->amount - $totalPaid }}" value="{{ $cheque->amount - $totalPaid }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control border-light shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Method</label>
                            <select name="payment_method" class="form-select border-light shadow-none" onchange="togglePaymentMethodDetails(this.value)" required>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Attach Document (Optional)</label>
                            <input type="file" name="document" class="form-control border-light shadow-none">
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div class="col-12 payment-method-field bank_transfer_fields">
                            <div class="p-3 bg-light rounded-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label extra-small fw-bold">Slected Bank</label>
                                        <select name="bank_id" class="form-select bg-white border-light shadow-none">
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label extra-small fw-bold">Reference Number</label>
                                        <input type="text" name="reference_number" class="form-control bg-white border-light shadow-none" placeholder="TXN-ID">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cheque Fields -->
                        <div class="col-12 payment-method-field cheque_fields" style="display:none">
                            <div class="p-3 bg-light rounded-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label extra-small fw-bold">Cheque Number</label>
                                        <input type="text" name="payment_cheque_number" class="form-control bg-white border-light shadow-none" placeholder="Enter cheque number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label extra-small fw-bold">Cheque Date</label>
                                        <input type="date" name="payment_cheque_date" class="form-control bg-white border-light shadow-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                            <textarea name="notes" class="form-control border-light shadow-none" rows="2" placeholder="Describe this payment..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 border-light" style="border-radius: 8px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: #6366f1; border: none; border-radius: 8px;">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update 3rd Party Modal -->
<div class="modal fade" id="updateThirdPartyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('cheques.update-third-party', $cheque) }}" method="POST">
                @csrf
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Update 3rd Party Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Payment Status</label>
                        <select name="third_party_payment_status" class="form-select border-light shadow-none">
                            <option value="pending" {{ $cheque->third_party_payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $cheque->third_party_payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Notes (Optional)</label>
                        <textarea name="third_party_notes" class="form-control border-light shadow-none" rows="3" placeholder="Additional details...">{{ $cheque->third_party_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 border-light" style="border-radius: 8px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: #6366f1; border: none; border-radius: 8px;">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePaymentMethodDetails(method) {
        document.querySelectorAll('.payment-method-field').forEach(div => div.style.display = 'none');
        if (method === 'bank_transfer') {
            document.querySelector('.bank_transfer_fields').style.display = 'block';
        } else if (method === 'cheque') {
            document.querySelector('.cheque_fields').style.display = 'block';
        }
    }
</script>

<style>
    .extra-small { font-size: 0.7rem; }
    .btn-icon:hover {
        background: #f3f4f6;
        color: #6366f1 !important;
    }
</style>
@endsection
