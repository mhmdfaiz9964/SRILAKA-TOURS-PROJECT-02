@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-gray-800">Role Management</h4>
            <p class="text-muted small mb-0">Manage user roles and their permissions</p>
        </div>
        @can('role-create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
            <i class="fa-solid fa-plus"></i> Create New Role
        </a>
        @endcan
    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i> {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($message = Session::get('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i> {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-bold small text-uppercase">Role Name</th>
                            <th class="py-3 text-muted fw-bold small text-uppercase">Permissions Count</th>
                            <th class="py-3 text-muted fw-bold small text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $role->name }}</td>
                            <td class="text-muted small">
                                <span class="badge bg-light text-dark border rounded-pill px-3">
                                    {{ $role->permissions->count() }} Permissions
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    @can('role-list')
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-light border-0 text-primary">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('role-edit')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-light border-0 text-success">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('role-delete')
                                        @if($role->name != 'Super Admin')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border-0 text-danger">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
