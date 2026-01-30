@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Update Out-Cheque: #{{ $outCheque->cheque_number }}</h4>
                    <p class="text-muted small">Update payee or status information.</p>
                </div>
                <a href="{{ route('out-cheques.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('out-cheques.update', $outCheque) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4 text-start">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Cheque Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Cheque Date</label>
                                        <input type="date" name="cheque_date" class="form-control border-light bg-light rounded-3 shadow-none" value="{{ old('cheque_date', $outCheque->cheque_date) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Cheque Number (6 Digits)</label>
                                        <input type="text" name="cheque_number" class="form-control border-light bg-light rounded-3 shadow-none @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number', $outCheque->cheque_number) }}" maxlength="6" minlength="6" placeholder="######" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Issuing Bank</label>
                                        <select name="bank_id" class="form-select border-light bg-light rounded-3 shadow-none" required>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ (old('bank_id', $outCheque->bank_id) == $bank->id) ? 'selected' : '' }}>{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount (LKR)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control border-light bg-light rounded-3 shadow-none" value="{{ old('amount', $outCheque->amount) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-start">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Payee Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Payee Name</label>
                                        <input type="text" name="payee_name" class="form-control border-light bg-light rounded-3 shadow-none" value="{{ old('payee_name', $outCheque->payee_name) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted text-uppercase">Update Status</label>
                                <select name="status" class="form-select border-light bg-light rounded-3 shadow-none">
                                    <option value="sent" {{ (old('status', $outCheque->status) == 'sent') ? 'selected' : '' }}>Sent (Pending)</option>
                                    <option value="realized" {{ (old('status', $outCheque->status) == 'realized') ? 'selected' : '' }}>Received (Cleared)</option>
                                    <option value="bounced" {{ (old('status', $outCheque->status) == 'bounced') ? 'selected' : '' }}>Bounced</option>
                                </select>
                            </div>

                            <div class="col-12 text-start">
                                <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                                <textarea name="notes" class="form-control border-light bg-light rounded-3 shadow-none" rows="3">{{ old('notes', $outCheque->notes) }}</textarea>
                            </div>

                            <div class="col-12 pt-3">
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-3" style="background: #6366f1; border: none;">
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
@endsection
