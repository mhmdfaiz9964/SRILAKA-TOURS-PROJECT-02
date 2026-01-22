@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header d-flex justify-content-between align-items-center">
        <h1 class="content-title">Banks</h1>
        <a href="{{ route('banks.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-plus me-2"></i> Add Bank
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">Bank Name</th>
                            <th class="py-3 border-0">Code</th>
                            <th class="py-3 border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banks as $bank)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bank-logo bg-light rounded-3" style="width: 40px; height: 40px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                        @if($bank->logo)
                                            <img src="{{ asset('storage/' . $bank->logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fa-solid fa-building-columns text-muted"></i>
                                        @endif
                                    </div>
                                    <p class="mb-0 fw-bold">{{ $bank->name }}</p>
                                </div>
                            </td>
                            <td>{{ $bank->code ?? '-' }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('banks.edit', $bank) }}" class="btn btn-sm btn-light text-primary rounded-3 border-0">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-danger rounded-3 border-0" 
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
                            <td colspan="3" class="text-center py-5 text-muted">No banks found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
