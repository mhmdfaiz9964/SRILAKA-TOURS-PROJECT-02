@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-0 fw-bold">Suppliers</h4>
                    <p class="text-muted small mb-0">Manage your suppliers and vendors</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @can('supplier-create')
                        <a href="{{ route('suppliers.create') }}"
                            class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2"
                            style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-plus"></i> Add Supplier
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <!-- Total Paid -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                        <div class="card-body p-3">
                            <div class="text-success small fw-bold text-uppercase mb-1">Total Paid Amount</div>
                            <div class="fw-bold text-success fs-4">LKR {{ number_format($totalPaid, 2) }}</div>
                        </div>
                    </div>
                </div>
                <!-- Total Outstanding -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                        <div class="card-body p-3">
                            <div class="text-danger small fw-bold text-uppercase mb-1">Total Outstanding Amount</div>
                            <div class="fw-bold text-danger fs-4">LKR {{ number_format($totalOutstanding, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="p-3 border-bottom bg-light bg-opacity-10">
                <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Search</label>
                        <div class="position-relative">
                            <i class="fa-solid fa-magnifying-glass position-absolute text-muted"
                                style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm ps-4 border-light rounded-3"
                                placeholder="Name, Company...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Sort By</label>
                        <select name="sort" class="form-select form-select-sm border-light rounded-3">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name_az" {{ request('sort') == 'name_az' ? 'selected' : '' }}>Name (A-Z)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm border-light rounded-3">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3"
                                style="background: #6366f1; border: none;">
                                <i class="fa-solid fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('suppliers.index') }}"
                                class="btn btn-light btn-sm px-3 rounded-3 border-light">
                                <i class="fa-solid fa-rotate-right me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $suppliers->total() }} Results
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase" style="width: 50px;">ID
                                </th>
                                <th class="py-3 text-muted fw-semibold small text-uppercase">Full Name</th>
                                <th class="py-3 text-muted fw-semibold small text-uppercase">Company</th>
                                <th class="py-3 text-muted fw-semibold small text-uppercase">Contact Number</th>
                                <!-- Credit Limit Removed -->
                                <th class="py-3 text-muted fw-semibold small text-uppercase">Outstanding</th>
                                <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                                <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                                <tr class="cursor-pointer"
                                    onclick="window.location='{{ route('suppliers.show', $supplier->id) }}'">
                                    <td class="ps-4 text-muted small">#{{ $supplier->id }}</td>
                                    <td class="fw-bold text-dark small">
                                        {{ $supplier->full_name }}
                                    </td>
                                    <td class="small">{{ $supplier->company_name ?? '-' }}</td>
                                    <td class="small">{{ $supplier->contact_number }}</td>
                                    <!-- Credit Limit Cell Removed -->
                                    <td class="small fw-bold text-danger">
                                        LKR {{ number_format($supplier->outstanding, 2) }}
                                    </td>
                                    <td class="small">
                                        @if($supplier->status)
                                            <span
                                                class="badge bg-success-subtle text-success rounded-pill border border-0">Active</span>
                                        @else
                                            <span
                                                class="badge bg-danger-subtle text-danger rounded-pill border border-0">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4" onclick="event.stopPropagation();">
                                        <div class="d-flex justify-content-end gap-1">
                                            @can('supplier-edit')
                                                <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                                    class="btn btn-sm btn-icon border-0 text-muted">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            @endcan
                                            @can('supplier-delete')
                                                <button type="button" class="btn btn-sm btn-icon border-0 text-muted"
                                                    onclick="confirmDelete({{ $supplier->id }}, 'delete-supplier-{{ $supplier->id }}')">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                                <form id="delete-supplier-{{ $supplier->id }}"
                                                    action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST"
                                                    class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
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
        .cursor-pointer {
            cursor: pointer;
        }

        .btn-icon:hover {
            background: #f3f4f6;
            color: #6366f1 !important;
        }
    </style>

    <script>
        function confirmDelete(id, formId) {
            Swal.fire({
                title: 'Delete Supplier?',
                text: "Are you sure you want to delete this supplier? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            })
        }
    </script>
@endsection