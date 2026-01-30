@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Record New Out-Cheque</h4>
                    <p class="text-muted small">Capture payments sent via cheque.</p>
                </div>
                <a href="{{ route('out-cheques.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('out-cheques.store') }}" method="POST">
                        @csrf
                        <div class="row g-4 text-start">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Cheque Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Cheque Date</label>
                                        <input type="date" name="cheque_date" class="form-control border-light bg-light rounded-3 shadow-none @error('cheque_date') is-invalid @enderror" value="{{ old('cheque_date', date('Y-m-d')) }}" required>
                                        @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Cheque Number (6 Digits)</label>
                                        <input type="text" name="cheque_number" class="form-control border-light bg-light rounded-3 shadow-none @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number') }}" maxlength="6" minlength="6" placeholder="######" required>
                                        @error('cheque_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Issuing Bank</label>
                                        <select name="bank_id" class="form-select border-light bg-light rounded-3 shadow-none @error('bank_id') is-invalid @enderror" required>
                                            <option value="">Select Bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount (LKR)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control border-light bg-light rounded-3 shadow-none @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">Payee Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Payee Name</label>
                                        <input type="text" name="payee_name" class="form-control border-light bg-light rounded-3 shadow-none @error('payee_name') is-invalid @enderror" value="{{ old('payee_name') }}" placeholder="Who is this payment for?" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                <select name="status" class="form-select border-light bg-light rounded-3 shadow-none">
                                    <option value="sent">Sent (Pending)</option>
                                    <option value="realized">Received (Cleared)</option>
                                    <option value="bounced">Bounced</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                                <textarea name="notes" class="form-control border-light bg-light rounded-3 shadow-none" rows="3">{{ old('notes') }}</textarea>
                            </div>

                            <div class="col-12 pt-3">
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-3" style="background: #6366f1; border: none;">
                                    Save Cheque Record
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
