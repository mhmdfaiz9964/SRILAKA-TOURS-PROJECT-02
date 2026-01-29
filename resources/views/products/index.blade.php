@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Products</h4>
                <p class="text-muted small mb-0">Manage all your products and inventory</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('product-create')
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> Add Product
                </a>
                @endcan
            </div>
        </div>
        <!-- Cost Value Card -->
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4" style="background: #ecfdf5;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-bold text-uppercase">Total Cost Value</span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #fff; color: #10b981;">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                        </div>
                        <div class="h4 fw-bold mb-0" style="color: #047857;">LKR {{ number_format($totalCostValue, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <form action="{{ route('products.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-grow-1">
                    <select class="form-select form-select-sm" style="width: 150px;" name="is_main_product" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="1" {{ request('is_main_product') == '1' ? 'selected' : '' }}>Main Product</option>
                        <option value="0" {{ request('is_main_product') == '0' ? 'selected' : '' }}>Sub Product</option>
                    </select>
                    
                    <select class="form-select form-select-sm" style="width: 150px;" name="sort" onchange="this.form.submit()">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="name_az" {{ request('sort') == 'name_az' ? 'selected' : '' }}>Name (A-Z)</option>
                         <option value="highest_price" {{ request('sort') == 'highest_price' ? 'selected' : '' }}>Highest Price</option>
                        <option value="lowest_price" {{ request('sort') == 'lowest_price' ? 'selected' : '' }}>Lowest Price</option>
                         <option value="highest_stock" {{ request('sort') == 'highest_stock' ? 'selected' : '' }}>Highest Stock</option>
                        <option value="lowest_stock" {{ request('sort') == 'lowest_stock' ? 'selected' : '' }}>Lowest Stock</option>
                    </select>

                     <input type="text" name="search" class="form-control form-control-sm" style="width: 200px;" placeholder="Search products..." value="{{ request('search') }}">
                     <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <a href="{{ route('products.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-rotate"></i></a>
                </form>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ count($products) }} Results</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase" style="width: 50px;">ID</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Name</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Units</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Stock</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Sold Stock</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Cost Price</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Cost Value</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Sale Price</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4 text-muted small">#{{ $product->id }}</td>
                            <td class="small fw-bold text-dark">{{ $product->name }}</td>
                            <td class="small">{{ $product->units ?? '-' }}</td>
                            <td class="small">
                                <span class="badge {{ $product->stock_alert > 0 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill border border-0">
                                    {{ $product->stock_alert }}
                                </span>
                            </td>
                            @php
                                $sold = \App\Models\SaleItem::where('product_id', $product->id)->sum('quantity');
                            @endphp
                            <td class="small fw-bold text-muted">{{ $sold }}</td>
                            <td class="small">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="small fw-bold text-success">{{ number_format($product->stock_alert * $product->cost_price, 2) }}</td>
                            <td class="small fw-bold text-primary">{{ number_format($product->sale_price, 2) }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    @can('product-edit')
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('product-delete')
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-muted" 
                                            onclick="confirmDelete({{ $product->id }}, 'delete-product-{{ $product->id }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-product-{{ $product->id }}" action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endcan
                                </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">No products found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-icon:hover {
        background: #f3f4f6;
        color: #6366f1 !important;
    }
</style>
@endsection
