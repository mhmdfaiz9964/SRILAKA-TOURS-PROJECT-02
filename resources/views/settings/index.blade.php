@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-gray-800">System Settings</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="fw-bold mb-3 text-primary">Company Details</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="{{ $settings['company_name'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Mobile / Phone</label>
                        <input type="text" class="form-control" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Company Logo</label>
                        <input type="file" class="form-control" name="company_logo" accept="image/*">
                        @if(!empty($settings['company_logo']))
                            <div class="mt-2 text-center border rounded p-2" style="width: 100px;">
                                <img src="{{ asset($settings['company_logo']) }}" alt="Logo" class="img-fluid" style="max-height: 50px;">
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Address</label>
                        <textarea class="form-control" name="company_address" rows="2">{{ $settings['company_address'] ?? '' }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    @can('settings-manage')
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fa-solid fa-save me-2"></i> Save Settings
                    </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
