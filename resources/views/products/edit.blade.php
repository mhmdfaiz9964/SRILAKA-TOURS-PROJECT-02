@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Edit Product</h1>
        <p class="text-muted small">Update product information.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <!-- Product Identification -->
                            <div class="col-12">
                                <h6 class="fw-bold text-muted text-uppercase small mb-3">Product Information</h6>
                            </div>
                            
                            <!-- Category and Type -->
                            <div class="col-12 mb-2">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Category</label>
                                            <div class="input-group">
                                                <select class="form-select" name="category_id" id="category_id">
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#createCategoryModal"><i class="fa-solid fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                             <label class="form-label fw-bold small">Product Type</label>
                                             <div class="d-flex gap-3 mt-1">
                                                 <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="is_main_product" id="mainProductYes" value="1" {{ $product->is_main_product ? 'checked' : '' }} onchange="toggleParentProduct()">
                                                    <label class="form-check-label small" for="mainProductYes">Main Product</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="is_main_product" id="mainProductNo" value="0" {{ !$product->is_main_product ? 'checked' : '' }} onchange="toggleParentProduct()">
                                                    <label class="form-check-label small" for="mainProductNo">Sub Product</label>
                                                </div>
                                             </div>
                                        </div>
                                        <div class="col-12 {{ $product->is_main_product ? 'd-none' : '' }}" id="parentProductField">
                                            <label class="form-label fw-bold small">Parent Product</label>
                                            <select class="form-select" name="parent_product_id">
                                                <option value="">Select Main Product</option>
                                                @foreach($mainProducts as $mainProduct)
                                                    <option value="{{ $mainProduct->id }}" {{ $product->parent_product_id == $mainProduct->id ? 'selected' : '' }}>{{ $mainProduct->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold small">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="code" class="form-label fw-bold small">Product Code / SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $product->code) }}" required>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="units" class="form-label fw-bold small">Units</label>
                                <input type="text" class="form-control @error('units') is-invalid @enderror" id="units" name="units" value="{{ old('units', $product->units) }}" placeholder="e.g. Kg, Pcs">
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
                                    <input type="number" class="form-control border-start-0 @error('stock_alert') is-invalid @enderror" id="stock_alert" name="stock_alert" value="{{ old('stock_alert', $product->stock_alert) }}">
                                </div>
                                @error('stock_alert') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="cost_price" class="form-label fw-bold small">Cost Price</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">$</span>
                                    <input type="number" step="0.01" class="form-control border-start-0 @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}">
                                </div>
                                @error('cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="sale_price" class="form-label fw-bold small">Sale Price</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">$</span>
                                    <input type="number" step="0.01" class="form-control border-start-0 @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}">
                                </div>
                                @error('sale_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5" style="background: #6366f1; border: none;">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createCategoryForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                         <label class="form-label fw-bold small">Code (Optional)</label>
                        <input type="text" class="form-control" name="code">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleParentProduct() {
        const isMain = document.getElementById('mainProductYes').checked;
        const field = document.getElementById('parentProductField');
        if(!isMain) {
            field.classList.remove('d-none');
        } else {
            field.classList.add('d-none');
        }
    }

    document.getElementById('createCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Add CSRF token
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("categories.store") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Add to select and select it
                const select = document.getElementById('category_id');
                const option = new Option(data.category.name, data.category.id);
                select.add(option, undefined);
                select.value = data.category.id;
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('createCategoryModal'));
                modal.hide();
                this.reset();
            } else {
                alert('Error creating category');
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
