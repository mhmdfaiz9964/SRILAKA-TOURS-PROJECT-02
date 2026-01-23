@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Edit Cheque #{{ $cheque->cheque_number }}</h4>
        <p class="text-muted small">Update cheque information</p>
    </div>

    <div class="row">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form action="{{ route('cheques.update', $cheque) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Cheque Number</label>
                                <input type="text" name="cheque_number" class="form-control border-light shadow-none @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number', $cheque->cheque_number) }}" required>
                                @error('cheque_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control border-light shadow-none @error('cheque_date') is-invalid @enderror" value="{{ old('cheque_date', $cheque->cheque_date) }}" required>
                                @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Select Bank</label>
                                <select name="bank_id" class="form-select border-light shadow-none @error('bank_id') is-invalid @enderror" required>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ $cheque->bank_id == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Amount (LKR)</label>
                                <input type="number" step="0.01" name="amount" class="form-control border-light shadow-none" value="{{ $cheque->amount }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Payer Name / Client Name</label>
                                <input type="text" name="payer_name" class="form-control border-light shadow-none" value="{{ $cheque->payer_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">3rd Part</label>
                                <input type="text" name="payee_name" class="form-control border-light shadow-none" value="{{ $cheque->payee_name }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Return Reason</label>
                                <select name="return_reason" class="form-select border-light shadow-none">
                                    <option value="">No Return</option>
                                    <option value="3rd Party" {{ $cheque->return_reason == '3rd Party' ? 'selected' : '' }}>3rd Party</option>
                                    <option value="JS Fabric" {{ $cheque->return_reason == 'JS Fabric' ? 'selected' : '' }}>JS Fabric</option>
                                    <option value="Customer" {{ $cheque->return_reason == 'Customer' ? 'selected' : '' }}>Customer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Payment Status</label>
                                <select name="payment_status" class="form-select border-light shadow-none">
                                    <option value="pending" {{ $cheque->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="partial paid" {{ $cheque->payment_status == 'partial paid' ? 'selected' : '' }}>Partial Paid</option>
                                    <option value="paid" {{ $cheque->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Notes</label>
                                <textarea name="notes" class="form-control border-light shadow-none" rows="3">{{ $cheque->notes }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: #6366f1; border: none; border-radius: 8px;">Update Cheque</button>
                            <a href="{{ route('cheques.index') }}" class="btn btn-light px-4 border-light" style="border-radius: 8px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
