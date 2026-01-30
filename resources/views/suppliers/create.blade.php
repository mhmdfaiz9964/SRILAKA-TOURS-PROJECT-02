@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Add New Supplier</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('suppliers.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-bold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="company_name" class="form-label fw-bold small">Company Name (Optional)</label>
                                <input type="text" class="form-control" id="company_name" name="company_name">
                            </div>
                            <div class="col-md-6">
                                <label for="contact_number" class="form-label fw-bold small">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                            </div>
                            <div class="col-md-6">
                                <label for="credit_limit" class="form-label fw-bold small">Credit Limit</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">LKR</span>
                                    <input type="number" step="0.01" class="form-control" id="credit_limit" name="credit_limit" value="0.00" min="0">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                                    <label class="form-check-label" for="status">Active Supplier</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5" style="background: #6366f1; border: none;">Save Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
