@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Suppliers</h4>
                <p class="text-muted small mb-0">Manage your suppliers and vendors</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> Add Supplier
                </a>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-filter me-1 text-black"></i> Filter
                    </button>
                    <!-- Search could go here -->
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ count($suppliers) }} Results</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase" style="width: 50px;">ID</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Full Name</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Company</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Contact Number</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('suppliers.show', $supplier->id) }}'">
                            <td class="ps-4 text-muted small">#{{ $supplier->id }}</td>
                            <td class="fw-bold text-dark small">
                                {{ $supplier->full_name }}
                            </td>
                            <td class="small">{{ $supplier->company_name ?? '-' }}</td>
                            <td class="small">{{ $supplier->contact_number }}</td>
                            <td class="small">
                                @if($supplier->status)
                                    <span class="badge bg-success-subtle text-success rounded-pill border border-0">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill border border-0">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-muted" 
                                            onclick="confirmDelete({{ $supplier->id }}, 'delete-supplier-{{ $supplier->id }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-supplier-{{ $supplier->id }}" action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">No suppliers found.</div>
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
    .cursor-pointer { cursor: pointer; }
    .btn-icon:hover {
        background: #f3f4f6;
        color: #6366f1 !important;
    }
</style>
@endsection
