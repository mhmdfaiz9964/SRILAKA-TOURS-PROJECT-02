@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width: 56px; height: 56px;">
                                <i class="fa-solid fa-user-gear text-primary fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">Profile Settings</h4>
                                <p class="text-muted small mb-0">Update your personal information and change your password
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Basic Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Full Name</label>
                                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                                class="form-control border-light rounded-3 shadow-none" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Email Address</label>
                                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                                class="form-control border-light rounded-3 shadow-none" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-5">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Change Password</h6>
                                    <p class="text-muted small mb-3">Leave blank if you don't want to change your password
                                    </p>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold text-muted mb-1">Current Password</label>
                                            <input type="password" name="current_password"
                                                class="form-control border-light rounded-3 shadow-none">
                                            <div class="form-text small">Required only if changing password</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted mb-1">New Password</label>
                                            <input type="password" name="new_password"
                                                class="form-control border-light rounded-3 shadow-none">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Confirm New
                                                Password</label>
                                            <input type="password" name="new_password_confirmation"
                                                class="form-control border-light rounded-3 shadow-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="{{ route('home') }}" class="btn btn-light rounded-pill px-4 shadow-sm">Cancel</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm"
                                    style="background: #6366f1; border: none;">
                                    <i class="fa-solid fa-save me-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection