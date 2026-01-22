@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header d-flex justify-content-between align-items-center">
        <h1 class="content-title">Cheques Management</h1>
        <a href="{{ route('cheques.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-plus me-2"></i> New Cheque
        </a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('cheques.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control rounded-pill" placeholder="Search No or Payer" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select rounded-pill">
                        <option value="">Payment Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial paid" {{ request('status') == 'partial paid' ? 'selected' : '' }}>Partial Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="cheque_status" class="form-select rounded-pill">
                        <option value="">Cheque Status</option>
                        <option value="processing" {{ request('cheque_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="approved" {{ request('cheque_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('cheque_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control rounded-pill" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control rounded-pill" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill"><i class="fa-solid fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">Cheque No</th>
                            <th class="py-3 border-0">Date</th>
                            <th class="py-3 border-0">Bank</th>
                            <th class="py-3 border-0 text-end">Amount</th>
                            <th class="py-3 border-0">Payee</th>
                            <th class="py-3 border-0">Status</th>
                            <th class="py-3 border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cheques as $cheque)
                        <tr>
                            <td class="ps-4">
                                @if($cheque->payment_status != 'paid')
                                    <a href="{{ route('cheques.show', $cheque) }}" class="fw-bold text-decoration-none">{{ $cheque->cheque_number }}</a>
                                @else
                                    <span class="fw-bold">{{ $cheque->cheque_number }}</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bank-sm-logo">
                                        @if($cheque->bank->logo)
                                            <img src="{{ asset('storage/' . $cheque->bank->logo) }}" width="24" height="24" style="object-fit: contain;">
                                        @else
                                            <i class="fa-solid fa-building-columns small text-muted"></i>
                                        @endif
                                    </div>
                                    <span>{{ $cheque->bank->name }}</span>
                                </div>
                            </td>
                            <td class="text-end fw-bold">LKR {{ number_format($cheque->amount, 2) }}</td>
                            <td>{{ $cheque->payer_name }}</td>
                            <td>
                                <span class="badge rounded-pill bg-soft-{{ $cheque->payment_status == 'paid' ? 'success' : ($cheque->payment_status == 'partial paid' ? 'warning' : 'danger') }}" 
                                      style="background: {{ $cheque->payment_status == 'paid' ? '#e8f5e9' : ($cheque->payment_status == 'partial paid' ? '#fff3e0' : '#ffebee') }}; 
                                             color: {{ $cheque->payment_status == 'paid' ? '#2e7d32' : ($cheque->payment_status == 'partial paid' ? '#ef6c00' : '#c62828') }};">
                                    {{ ucwords($cheque->payment_status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('cheques.edit', $cheque) }}" class="btn btn-sm btn-light text-primary rounded-3 border-0">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-danger rounded-3 border-0" 
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
                            <td colspan="7" class="text-center py-5 text-muted">No cheques found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-top">
                {{ $cheques->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
