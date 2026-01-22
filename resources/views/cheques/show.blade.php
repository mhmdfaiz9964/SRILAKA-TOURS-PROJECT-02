@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title mb-0">Cheque Details</h1>
            <p class="text-muted">#{{ $cheque->cheque_number }} | {{ $cheque->bank->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cheques.index') }}" class="btn btn-light rounded-pill px-4">Back to List</a>
            @if($cheque->payment_status != 'paid')
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    <i class="fa-solid fa-plus me-2"></i> Add Payment
                </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-muted small text-uppercase mb-4">Summary</h6>
                    <div class="mb-4 text-center">
                        <div class="display-6 fw-bold">LKR {{ number_format($cheque->amount, 2) }}</div>
                        <p class="text-muted small">Total amount on cheque</p>
                    </div>
                    
                    <ul class="list-group list-group-flush border-0">
                        <li class="list-group-item d-flex justify-content-between px-0 py-3 border-0 border-bottom">
                            <span class="text-muted">Paid Amount</span>
                            <span class="fw-bold text-success">LKR {{ number_format($totalPaid, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-3 border-0 border-bottom">
                            <span class="text-muted">Remaining</span>
                            <span class="fw-bold text-danger">LKR {{ number_format($cheque->amount - $totalPaid, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-muted small text-uppercase mb-4">Payment History</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0">Date</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cheque->payments as $payment)
                                <tr>
                                    <td class="ps-4">{{ $payment->payment_date }}</td>
                                    <td class="fw-bold text-success">LKR {{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-muted">{{ $payment->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">No payments recorded yet.</td>
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
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="cheque_id" value="{{ $cheque->id }}">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" max="{{ $cheque->amount - $totalPaid }}" value="{{ $cheque->amount - $totalPaid }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
