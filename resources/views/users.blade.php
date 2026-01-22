@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header d-flex justify-content-between align-items-center">
        <h1 class="content-title">Users Management</h1>
        <button class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-plus me-2"></i> Add User
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 10px; background: #efedff; color: #6c5ce7; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        A
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold">MR Admin</p>
                                        <span class="small text-muted">ID: #1</span>
                                    </div>
                                </div>
                            </td>
                            <td>admin@selfholidays.com</td>
                            <td><span class="badge bg-soft-primary text-primary" style="background: #efedff;">Administrator</span></td>
                            <td><span class="badge bg-success rounded-pill">Active</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-primary me-2"><i class="fa-solid fa-edit"></i></button>
                                <button class="btn btn-sm btn-light text-danger"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
