@extends('layouts.app')

@section('content')
<div class="container-fluid">
     <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-gray-800">Role Details: {{ $role->name }}</h4>
            <p class="text-muted small mb-0">View role permissions</p>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">Assigned Permissions</h5>
                    
                    <div class="row g-4">
                        @foreach($permissionGroups as $groupName => $permissions)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border rounded-3 shadow-sm">
                                <div class="card-header bg-light border-bottom py-2">
                                    <span class="fw-bold small text-uppercase">{{ $groupName }}</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($permissions as $permission)
                                        <span class="badge bg-light text-dark border">{{ $permission->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
