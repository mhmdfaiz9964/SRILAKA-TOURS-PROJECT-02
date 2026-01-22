@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1 class="content-title">Edit Cheque #{{ $cheque->cheque_number }}</h1>
    </div>

    <div class="row">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('cheques.update', $cheque) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cheque Number</label>
                                <input type="text" name="cheque_number" class="form-control @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number', $cheque->cheque_number) }}" required>
                                @error('cheque_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control @error('cheque_date') is-invalid @enderror" value="{{ old('cheque_date', $cheque->cheque_date) }}" required>
                                @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Bank</label>
                                <select name="bank_id" class="form-select @error('bank_id') is-invalid @enderror" required>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ $cheque->bank_id == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reason</label>
                                <select name="cheque_reason_id" class="form-select" required>
                                    @foreach($reasons as $reason)
                                        <option value="{{ $reason->id }}" {{ $cheque->cheque_reason_id == $reason->id ? 'selected' : '' }}>{{ $reason->reason }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Amount (LKR)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $cheque->amount }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="pending" {{ $cheque->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="partial paid" {{ $cheque->payment_status == 'partial paid' ? 'selected' : '' }}>Partial Paid</option>
                                    <option value="paid" {{ $cheque->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Cheque Status</label>
                                <select name="cheque_status" class="form-select">
                                    <option value="processing" {{ $cheque->cheque_status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="approved" {{ $cheque->cheque_status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $cheque->cheque_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payer Name</label>
                                <input type="text" name="payer_name" class="form-control" value="{{ $cheque->payer_name }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ $cheque->notes }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Cheque</button>
                            <a href="{{ route('cheques.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
