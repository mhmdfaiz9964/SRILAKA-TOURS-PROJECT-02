@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1 class="content-title">Edit Bank</h1>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('banks.update', $bank) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Bank Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $bank->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label fw-bold">Bank Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $bank->code) }}">
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="logo" class="form-label fw-bold">Bank Logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*" onchange="previewImage(event)">
                            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="mt-3 d-flex gap-3 align-items-center">
                                <div>
                                    <p class="small text-muted mb-1">Current:</p>
                                    @if($bank->logo)
                                        <img src="{{ asset('storage/' . $bank->logo) }}" alt="Current Logo" style="width: 80px; height: 80px; object-fit: contain; border-radius: 8px; border: 1px solid #eee;">
                                    @else
                                        <span class="badge bg-light text-muted">No Logo</span>
                                    @endif
                                </div>
                                <div id="preview-container" style="display: none;">
                                    <p class="small text-muted mb-1">New Preview:</p>
                                    <img id="logo-preview" src="#" alt="Logo Preview" style="width: 80px; height: 80px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd; padding: 5px;">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Bank</button>
                            <a href="{{ route('banks.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('logo-preview');
            output.src = reader.result;
            document.getElementById('preview-container').style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection
