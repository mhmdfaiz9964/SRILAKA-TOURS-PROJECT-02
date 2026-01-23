@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Cheques Management</h4>
                <p class="text-muted small mb-0">Track and manage all incoming/outgoing cheques</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm bg-white border-light shadow-sm px-3">
                    <i class="fa-solid fa-file-export me-1"></i> Export
                </button>
                <a href="{{ route('cheques.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> New Cheque
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body py-3">
            <form action="{{ route('cheques.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-light shadow-none" placeholder="Search No or Payer" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm border-light shadow-none">
                        <option value="">Payment Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial paid" {{ request('status') == 'partial paid' ? 'selected' : '' }}>Partial Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm border-light shadow-none" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm border-light shadow-none" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-dark btn-sm w-100"><i class="fa-solid fa-filter"></i></button>
                </div>
                <div class="col text-end">
                    <a href="{{ route('cheques.index') }}" class="btn btn-link btn-sm text-muted text-decoration-none">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button onclick="updateSystem()" class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-rotate-right me-1 text-muted"></i> Update
                    </button>
                    <button class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-filter me-1 text-muted"></i> Sort
                    </button>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ $cheques->total() }} Results</span>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm px-3 border-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-gear me-1 text-muted"></i> View
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase" style="width: 50px;">
                                <input type="checkbox" class="form-check-input">
                            </th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Cheque Detail</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Bank</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Amount</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Payee</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Status</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cheques as $cheque)
                        <tr>
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('cheques.show', $cheque) }}" class="fw-bold text-primary small text-decoration-none hover-underline">{{ $cheque->cheque_number }}</a>
                                    <span class="text-muted extra-small">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d M, Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bank-avatar" style="width: 24px; height: 24px; border-radius: 6px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @if($cheque->bank->logo)
                                            <img src="{{ asset('storage/' . $cheque->bank->logo) }}" alt="L" style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fa-solid fa-building-columns text-muted" style="font-size: 0.7rem;"></i>
                                        @endif
                                    </div>
                                    <span class="small">{{ $cheque->bank->name }}</span>
                                </div>
                            </td>
                            <td class="small fw-semibold">LKR {{ number_format($cheque->amount, 2) }}</td>
                            <td class="small">{{ $cheque->payer_name }}</td>
                            <td>
                                @php
                                    $statusColor = match($cheque->payment_status) {
                                        'paid' => '#10b981',
                                        'partial paid' => '#f59e0b',
                                        default => '#ef4444'
                                    };
                                @endphp
                                <div class="d-flex align-items-center gap-1">
                                    <span class="status-dot" style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor }};"></span>
                                    <span class="small fw-medium" style="color: {{ $statusColor }};">{{ ucwords($cheque->payment_status) }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('cheques.edit', $cheque) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-muted" 
                                            onclick="confirmDelete({{ $cheque->id }}, 'delete-cheque-{{ $cheque->id }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-cheque-{{ $cheque->id }}" action="{{ route('cheques.destroy', $cheque) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted small">No cheques found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="small text-muted">Page {{ $cheques->currentPage() }} of {{ $cheques->lastPage() }}</div>
                <div class="pagination-container">
                    {{ $cheques->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small { font-size: 0.75rem; }
    .btn-icon:hover {
        background: #f3f4f6;
        color: #6366f1 !important;
    }
    .pagination { margin-bottom: 0; gap: 4px; }
    .page-link { 
        padding: 5px 12px; 
        font-size: 0.8rem; 
        border-radius: 6px !important; 
        border-color: #f3f4f6;
        color: #6b7280;
    }
    .page-item.active .page-link {
        background: #6366f1;
        border-color: #6366f1;
    }
    .hover-underline:hover {
        text-decoration: underline !important;
    }
</style>
@endsection
