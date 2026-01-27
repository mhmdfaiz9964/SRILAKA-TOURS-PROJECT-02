@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Add New Investor</h4>
                    <p class="text-muted small">Register a new investment record in the system.</p>
                </div>
                <a href="{{ route('investors.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('investors.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3">Basic Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Investor Name</label>
                                        <input type="text" name="name" class="form-control border-light bg-light rounded-3 shadow-none @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter full name" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                        <select name="status" class="form-select border-light bg-light rounded-3 shadow-none @error('status') is-invalid @enderror">
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="waiting" {{ old('status') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Investment Details -->
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3">Investment Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Invest Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-light text-muted small">LKR</span>
                                            <input type="number" step="0.01" name="invest_amount" class="form-control border-light bg-light shadow-none @error('invest_amount') is-invalid @enderror" value="{{ old('invest_amount') }}" required>
                                        </div>
                                        @error('invest_amount') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Expect Profit</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-light text-muted small">LKR</span>
                                            <input type="number" step="0.01" name="expect_profit" class="form-control border-light bg-light shadow-none @error('expect_profit') is-invalid @enderror" value="{{ old('expect_profit') }}" required>
                                        </div>
                                        @error('expect_profit') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Paid Profit</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-light text-muted small">LKR</span>
                                            <input type="number" step="0.01" name="paid_profit" class="form-control border-light bg-light shadow-none @error('paid_profit') is-invalid @enderror" value="{{ old('paid_profit', 0) }}">
                                        </div>
                                        @error('paid_profit') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Important Dates -->
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3">Important Dates</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Collect Date</label>
                                        <input type="date" name="collect_date" class="form-control border-light bg-light rounded-3 shadow-none @error('collect_date') is-invalid @enderror" value="{{ old('collect_date') }}">
                                        @error('collect_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Refund Date</label>
                                        <input type="date" name="refund_date" class="form-control border-light bg-light rounded-3 shadow-none @error('refund_date') is-invalid @enderror" value="{{ old('refund_date') }}">
                                        @error('refund_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-12">
                                <h6 class="fw-bold text-primary text-uppercase small mb-3">Additional Information</h6>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Internal Notes</label>
                                    <textarea name="notes" class="form-control border-light bg-light rounded-3 shadow-none" rows="4" placeholder="Any additional details or special conditions...">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 pt-3">
                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-3" style="background: #6366f1; border: none;">
                                        Save Investor Record
                                    </button>
                                    <a href="{{ route('investors.index') }}" class="btn btn-light px-4 py-2 fw-bold border-light rounded-3">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
