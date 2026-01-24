@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Add New Product</h1>
        <p class="text-muted small">Create a new product in your inventory.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('products.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <!-- Product Identification -->
                            <div class="col-12">
                                <h6 class="fw-bold text-muted text-uppercase small mb-3">Product Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold small">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label fw-bold small">Product Code / SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="units" class="form-label fw-bold small">Units</label>
                                <input type="text" class="form-control @error('units') is-invalid @enderror" id="units" name="units" value="{{ old('units') }}" placeholder="e.g. Kg, Pcs">
                                @error('units') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Inventory & Pricing -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-muted text-uppercase small mb-3">Inventory & Pricing</h6>
                            </div>

                            <div class="col-md-4">
                                <label for="stock_alert" class="form-label fw-bold small">Stock Alert Level</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-bell text-muted"></i></span>
                                    <input type="number" class="form-control border-start-0 @error('stock_alert') is-invalid @enderror" id="stock_alert" name="stock_alert" value="{{ old('stock_alert', 0) }}">
                                </div>
                                @error('stock_alert') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="cost_price" class="form-label fw-bold small">Cost Price</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">$</span>
                                    <input type="number" step="0.01" class="form-control border-start-0 @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', 0) }}">
                                </div>
                                @error('cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="sale_price" class="form-label fw-bold small">Sale Price</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">$</span>
                                    <input type="number" step="0.01" class="form-control border-start-0 @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}">
                                </div>
                                @error('sale_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5" style="background: #6366f1; border: none;">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
