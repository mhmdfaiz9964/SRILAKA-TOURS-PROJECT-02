@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Update In-Cheque: #{{ $inCheque->cheque_number }}</h4>
                    <p class="text-muted small">Update cheque details or change status.</p>
                </div>
                <a href="{{ route('in-cheques.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('in-cheques.update', $inCheque) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4 text-start">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Cheque Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Cheque Date</label>
                                        <input type="date" name="cheque_date" class="form-control border-light bg-light rounded-3 shadow-none @error('cheque_date') is-invalid @enderror" value="{{ old('cheque_date', $inCheque->cheque_date) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Cheque Number</label>
                                        <input type="text" name="cheque_number" class="form-control border-light bg-light rounded-3 shadow-none @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number', $inCheque->cheque_number) }}" maxlength="6" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Bank</label>
                                        <select name="bank_id" class="form-select border-light bg-light rounded-3 shadow-none" required>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ (old('bank_id', $inCheque->bank_id) == $bank->id) ? 'selected' : '' }}>{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount (LKR)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control border-light bg-light rounded-3 shadow-none" value="{{ old('amount', $inCheque->amount) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Source Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Payer Name</label>
                                        <input type="text" name="payer_name" class="form-control border-light bg-light rounded-3 shadow-none text-start" value="{{ old('payer_name', $inCheque->payer_name) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Status & Actions</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Current Status</label>
                                        <select name="status" id="statusSelect" class="form-select border-light bg-light rounded-3 shadow-none text-start" onchange="toggleThirdPartyFields()">
                                            <option value="received" {{ (old('status', $inCheque->status) == 'received') ? 'selected' : '' }}>Received (In Hand)</option>
                                            <option value="deposited" {{ (old('status', $inCheque->status) == 'deposited') ? 'selected' : '' }}>Deposited</option>
                                            <option value="transferred_to_third_party" {{ (old('status', $inCheque->status) == 'transferred_to_third_party') ? 'selected' : '' }}>Transferred to 3rd Party</option>
                                            <option value="realized" {{ (old('status', $inCheque->status) == 'realized') ? 'selected' : '' }}>Realized</option>
                                            <option value="returned" {{ (old('status', $inCheque->status) == 'returned') ? 'selected' : '' }}>Returned</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="thirdPartyField" style="{{ (old('status', $inCheque->status) == 'transferred_to_third_party') ? 'display: block;' : 'display: none;' }}">
                                        <label class="form-label small fw-bold text-muted text-uppercase">3rd Party Name</label>
                                        <input type="text" name="third_party_name" class="form-control border-light bg-light rounded-3 shadow-none text-start" value="{{ old('third_party_name', $inCheque->third_party_name) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-start">
                                <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                                <textarea name="notes" class="form-control border-light bg-light rounded-3 shadow-none" rows="3">{{ old('notes', $inCheque->notes) }}</textarea>
                            </div>

                            <div class="col-12 pt-3">
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-3 text-start" style="background: #6366f1; border: none;">
                                    Update Cheque Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleThirdPartyFields() {
    var status = document.getElementById('statusSelect').value;
    var field = document.getElementById('thirdPartyField');
    if (status === 'transferred_to_third_party') {
        field.style.display = 'block';
    } else {
        field.style.display = 'none';
    }
}
</script>
@endsection
