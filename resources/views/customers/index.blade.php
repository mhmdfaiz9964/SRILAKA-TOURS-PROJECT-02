@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Customers</h4>
                <p class="text-muted small mb-0">Manage customer relationships and credit limits</p>
            </div>

            <!-- Total Outstanding Badge/Button - Centered -->
            <!-- Spacer -->
            <div></div>

            <div class="d-flex align-items-center gap-2">
                @can('customer-create')
                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none; padding: 0.6rem 1rem;">
                    <i class="fa-solid fa-plus"></i> Add Customer
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4">
             <a href="{{ request('outstanding_only') == '1' ? route('customers.index', request()->except('outstanding_only')) : route('customers.index', array_merge(request()->query(), ['outstanding_only' => 1])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 transition-all {{ request('outstanding_only') == '1' ? 'ring-2 ring-primary' : '' }}" style="background: #fff7ed;">
                    <div class="card-body p-3">
                         <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-bold text-uppercase" style="color: #9a3412 !important;">Total Outstanding</span>
                             <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; background: #fff; color: #f97316;">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="h4 fw-bold mb-0" style="color: #c2410c;">LKR {{ number_format($totalOutstanding, 2) }}</div>
                        <div class="small mt-1 text-muted" style="font-size:0.75rem;">
                            @if(request('outstanding_only'))
                                <span class="text-danger fw-bold"><i class="fa-solid fa-filter me-1"></i> Filter Active</span>
                            @else
                                Click to filter outstanding customers
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-3 align-items-end">
                @if(request('outstanding_only'))
                    <input type="hidden" name="outstanding_only" value="1">
                @endif
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="Name, Phone...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Sort By</label>
                    <select name="sort" class="form-select form-select-sm border-light rounded-3">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="highest_amount" {{ request('sort') == 'highest_amount' ? 'selected' : '' }}>Highest Limit</option>
                        <option value="lowest_amount" {{ request('sort') == 'lowest_amount' ? 'selected' : '' }}>Lowest Limit</option>
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
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $customers->total() }} Results</div>
                </div>
            </form>
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
    .hover-shadow-md {
        transition: all 0.2s ease;
    }
    .hover-shadow-md:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-1px);
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
</style>
@endsection
