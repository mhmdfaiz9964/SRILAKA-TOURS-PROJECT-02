@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Add New Expense</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('expenses.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Reason / Description</label>
                            <input type="text" name="reason" class="form-control bg-light border-light shadow-none rounded-3" required placeholder="e.g. Salary for Staff">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Amount (LKR)</label>
                                <input type="number" step="0.01" name="amount" class="form-control bg-light border-light shadow-none rounded-3" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Date</label>
                                <input type="date" name="expense_date" class="form-control bg-light border-light shadow-none rounded-3" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Paid By (Optional)</label>
                            <input type="text" name="paid_by" class="form-control bg-light border-light shadow-none rounded-3" placeholder="Who paid/authorized this?">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select bg-light border-light shadow-none rounded-3" onchange="toggleChequeFields()">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div id="cheque_fields" class="p-3 bg-light rounded-3 mb-3 d-none">
                            <h6 class="fw-bold text-muted mb-3 small text-uppercase">Cheque Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Cheque Number (6 Digits)</label>
                                    <input type="text" name="cheque_number" class="form-control shadow-none rounded-3" maxlength="6" placeholder="000000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Cheque Date</label>
                                    <input type="date" name="cheque_date" class="form-control shadow-none rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Bank</label>
                                    <select name="bank_id" class="form-select shadow-none rounded-3">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Payer Name</label>
                                    <input type="text" name="payer_name" class="form-control shadow-none rounded-3" placeholder="Name on Cheque">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                            <textarea name="notes" class="form-control bg-light border-light shadow-none rounded-3" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('expenses.index') }}" class="btn btn-light px-4 rounded-3">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 rounded-3" style="background: #6366f1; border: none;">Save Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleChequeFields() {
        const method = document.getElementById('payment_method').value;
        const fields = document.getElementById('cheque_fields');
        if (method === 'cheque') {
            fields.classList.remove('d-none');
        } else {
            fields.classList.add('d-none');
        }
    }
</script>
@endsection
