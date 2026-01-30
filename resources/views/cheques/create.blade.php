@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Add New Cheque</h4>
        <p class="text-muted small">Register a new cheque into the system</p>
    </div>

    <div class="row">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form action="{{ route('cheques.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Cheque Number (6 Digits)</label>
                                <input type="text" name="cheque_number" id="cheque_number_input" class="form-control border-light shadow-none @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number') }}" placeholder="######" required maxlength="6" minlength="6">
                                <div id="cheque_validation_msg" class="small mt-1 d-none"></div>
                                @error('cheque_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control border-light shadow-none @error('cheque_date') is-invalid @enderror" value="{{ old('cheque_date') }}" required>
                                @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Select Bank</label>
                                <select name="bank_id" class="form-select border-light shadow-none @error('bank_id') is-invalid @enderror" required>
                                    <option value="">Choose Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Amount (LKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-light text-muted small">LKR</span>
                                    <input type="number" step="0.01" name="amount" class="form-control border-light shadow-none @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="0.00" required>
                                </div>
                                @error('amount') <div class="invalid-feedback text-danger small mt-1 d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Payer Name / Client Name</label>
                                <input type="text" name="payer_name" class="form-control border-light shadow-none @error('payer_name') is-invalid @enderror" value="{{ old('payer_name') }}" placeholder="Whom gave this cheque" required>
                                @error('payer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">3rd Part (Optional)</label>
                                <input type="text" name="payee_name" class="form-control border-light shadow-none @error('payee_name') is-invalid @enderror" value="{{ old('payee_name') }}" placeholder="Specify 3rd party if any">
                                @error('payee_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Return Reason (If applicable)</label>
                                <select name="return_reason" class="form-select border-light shadow-none">
                                    <option value="">No Return</option>
                                    <option value="3rd Party" {{ old('return_reason') == '3rd Party' ? 'selected' : '' }}>3rd Party</option>
                                    <option value="JS Fabric" {{ old('return_reason') == 'JS Fabric' ? 'selected' : '' }}>JS Fabric</option>
                                    <option value="Customer" {{ old('return_reason') == 'Customer' ? 'selected' : '' }}>Customer</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Notes</label>
                                <textarea name="notes" class="form-control border-light shadow-none" rows="3" placeholder="Additional details...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: #6366f1; border: none; border-radius: 8px;">Create Cheque</button>
                            <a href="{{ route('cheques.index') }}" class="btn btn-light px-4 border-light" style="border-radius: 8px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('cheque_number_input').addEventListener('input', function(e) {
    const val = e.target.value;
    const msg = document.getElementById('cheque_validation_msg');
    
    // Remove non-numeric characters
    e.target.value = val.replace(/[^0-9]/g, '');
    
    if (e.target.value.length === 6) {
        msg.textContent = '✓ Perfect: 6 digits entered';
        msg.className = 'small mt-1 text-success d-block';
        e.target.classList.remove('border-danger');
        e.target.classList.add('border-success');
    } else if (e.target.value.length > 0) {
        msg.textContent = 'Keep typing... Need ' + (6 - e.target.value.length) + ' more digits';
        msg.className = 'small mt-1 text-warning d-block';
        e.target.classList.remove('border-success');
        e.target.classList.add('border-warning');
    } else {
        msg.className = 'small mt-1 d-none';
        e.target.classList.remove('border-success', 'border-warning');
    }
});
</script>
@endpush
