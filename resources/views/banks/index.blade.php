@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h4 class="mb-0 fw-bold">Banks</h4>
                <p class="text-muted small mb-0">Manage all registered banks and their codes</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm bg-white border-light shadow-sm px-3">
                    <i class="fa-solid fa-file-export me-1"></i> Export
                </button>
                <a href="{{ route('banks.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> Add Bank
                </a>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button onclick="updateSystem()" class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-rotate-right me-1 text-black"></i> Update
                    </button>
                    <button class="btn btn-light btn-sm px-3 border-light">
                        <i class="fa-solid fa-filter me-1 text-black"></i> Filter
                    </button>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ count($banks) }} Results</span>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm px-3 border-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-arrow-down-short-wide me-1 text-black"></i> Short
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
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Bank Name</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase">Bank Code</th>
                            <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banks as $bank)
                        <tr>
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bank-avatar" style="width: 36px; height: 36px; border-radius: 10px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @if($bank->logo)
                                            <img src="{{ asset('storage/' . $bank->logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fa-solid fa-building-columns text-muted" style="font-size: 0.95rem;"></i>
                                        @endif
                                    </div>
                                    <div class="fw-bold text-dark small" style="font-size: 0.95rem;">{{ $bank->name }}</div>
                                </div>
                            </td>
                            <td class="small">{{ $bank->code ?? '-' }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('banks.edit', $bank) }}" class="btn btn-sm btn-icon border-0 text-muted">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-muted" 
                                            onclick="confirmDelete({{ $bank->id }}, 'delete-bank-{{ $bank->id }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-bank-{{ $bank->id }}" action="{{ route('banks.destroy', $bank) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted">No banks found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="small text-muted">Showing {{ count($banks) }} results</div>
                <div class="pagination-custom d-flex gap-1">
                    <button class="btn btn-light btn-sm px-2 text-muted border"><i class="fa-solid fa-chevron-left fa-xs"></i></button>
                    <button class="btn btn-primary btn-sm px-2 px-3 border" style="background: #6366f1; border: none;">1</button>
                    <button class="btn btn-light btn-sm px-2 text-muted border"><i class="fa-solid fa-chevron-right fa-xs"></i></button>
                </div>
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
