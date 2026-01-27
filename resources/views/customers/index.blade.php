@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Customers</h4>
                <p class="text-muted small mb-0">Manage customer relationships and credit limits</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('customer-create')
                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> Add Customer
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <form action="{{ route('customers.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1">
                     <input type="text" name="search" class="form-control form-control-sm" style="width: 250px;" placeholder="Search name, phone..." value="{{ request('search') }}">
                     <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-rotate"></i></a>
                </form>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ count($customers) }} Results</span>
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
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Mobile</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Credit Limit</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Outstanding</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('customers.show', $customer->id) }}'">
                            <td class="ps-4 text-muted small">#{{ $customer->id }}</td>
                            <td class="fw-bold text-dark small">
                                {{ $customer->full_name }}
                            </td>
                            <td class="small">{{ $customer->company_name ?? '-' }}</td>
                            <td class="small">{{ $customer->mobile_number }}</td>
                            <td class="small">
                                <span class="badge {{ $customer->outstanding >= ($customer->credit_limit ?? 0) ? 'bg-danger' : 'bg-success' }} rounded-pill">
                                    {{ number_format($customer->credit_limit, 2) }}
                                </span>
                            </td>
                            <td class="small fw-bold {{ $customer->outstanding >= ($customer->credit_limit ?? 0) ? 'text-danger' : 'text-success' }}">
                                {{ number_format($customer->outstanding, 2) }}
                            </td>
                            <td class="small">
                                @if($customer->status)
                                    <span class="badge bg-success-subtle text-success rounded-pill border border-0">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill border border-0">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <div class="d-flex justify-content-end gap-1">
                                    @can('customer-edit')
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('customer-delete')
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-muted" 
                                            onclick="confirmDelete({{ $customer->id }}, 'delete-customer-{{ $customer->id }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-customer-{{ $customer->id }}" action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">No customers found.</div>
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
