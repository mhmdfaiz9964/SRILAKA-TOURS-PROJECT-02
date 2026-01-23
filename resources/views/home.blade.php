@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">Financial Overview</h4>
                    <p class="text-muted small mb-0">Monitor your banks, users and cheque activities</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white btn-sm shadow-sm border-light px-3">
                        <i class="fa-solid fa-calendar-day me-1"></i> Today
                    </button>
                    <button class="btn btn-primary btn-sm shadow-sm px-3" style="background: #6366f1; border: none;">
                        <i class="fa-solid fa-download me-1"></i> Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Welcome Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="p-2 rounded-3 bg-white bg-opacity-20 text-black">
                            <i class="fa-solid fa-face-smile-beam fa-lg"></i>
                        </div>
                    </div>
                    <p class="mb-1 text-white text-opacity-75 small">Welcome Back,</p>
                    <h4 class="fw-bold mb-0 text-truncate">{{ Auth::user()->name }}</h4>
                    <div class="mt-3 small py-1 px-2 rounded-pill bg-white bg-opacity-10 d-inline-flex align-items-center">
                        <i class="fa-solid fa-circle-check fa-xs me-1"></i> Account Active
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="p-3 rounded-3" style="background: #f5f3ff; color: #000;">
                            <i class="fa-solid fa-users fa-lg"></i>
                        </div>
                        <span class="badge rounded-pill text-success bg-success bg-opacity-10 py-2 px-3">+2% <i class="fa-solid fa-arrow-up fa-xs"></i></span>
                    </div>
                    <p class="text-muted small mb-1">Total System Users</p>
                    <h3 class="fw-bold mb-0">{{ $userCount }}</h3>
                </div>
            </div>
        </div>

        <!-- Banks Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="p-3 rounded-3" style="background: #ecfdf5; color: #000;">
                            <i class="fa-solid fa-building-columns fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Registered Banks</p>
                    <h3 class="fw-bold mb-0">{{ $bankCount }}</h3>
                </div>
            </div>
        </div>

        <!-- Cheques Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="p-3 rounded-3" style="background: #fffbeb; color: #000;">
                            <i class="fa-solid fa-money-check-dollar fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Total Cheques</p>
                    <h3 class="fw-bold mb-0">{{ $chequeCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Cheque Activities</h5>
                    <a href="{{ route('cheques.index') }}" class="btn btn-light btn-sm px-3 border-light">View All</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="border-0 small text-muted text-uppercase fw-bold ps-0">Cheque Detail</th>
                                    <th class="border-0 small text-muted text-uppercase fw-bold">Payee</th>
                                    <th class="border-0 small text-muted text-uppercase fw-bold">Status</th>
                                    <th class="border-0 small text-muted text-uppercase fw-bold text-end pe-0">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCheques as $cheque)
                                <tr>
                                    <td class="ps-0 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bank-icon p-2 rounded-circle bg-light">
                                                <i class="fa-solid fa-money-bill-transfer text-muted fa-sm"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ $cheque->cheque_number }}</div>
                                                <div class="text-muted extra-small">{{ $cheque->bank->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="small fw-medium">{{ $cheque->payer_name }}</span></td>
                                    <td>
                                        @php
                                            $dotColor = match($cheque->payment_status) {
                                                'paid' => '#10b981',
                                                'partial paid' => '#f59e0b',
                                                default => '#ef4444'
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="rounded-circle" style="width: 6px; height: 6px; background: {{ $dotColor }};"></span>
                                            <span class="small" style="color: {{ $dotColor }};">{{ ucwords($cheque->payment_status) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-0"><span class="small text-muted">{{ $cheque->created_at->diffForHumans() }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">No recent activities found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions / Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fafafa;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('cheques.create') }}" class="btn btn-white border-light shadow-sm text-start p-3 d-flex align-items-center gap-3">
                            <div class="p-2 rounded bg-primary bg-opacity-10 text-black"><i class="fa-solid fa-plus"></i></div>
                            <div>
                                <div class="fw-bold small">Add New Cheque</div>
                                <div class="extra-small text-muted">Register an incoming cheque</div>
                            </div>
                        </a>
                        <a href="{{ route('banks.create') }}" class="btn btn-white border-light shadow-sm text-start p-3 d-flex align-items-center gap-3">
                            <div class="p-2 rounded bg-success bg-opacity-10 text-black"><i class="fa-solid fa-building-columns"></i></div>
                            <div>
                                <div class="fw-bold small">Register Bank</div>
                                <div class="extra-small text-muted">Add a new bank to the system</div>
                            </div>
                        </a>
                        <a href="{{ route('users.create') }}" class="btn btn-white border-light shadow-sm text-start p-3 d-flex align-items-center gap-3">
                            <div class="p-2 rounded bg-info bg-opacity-10 text-black"><i class="fa-solid fa-user-plus"></i></div>
                            <div>
                                <div class="fw-bold small">Create User</div>
                                <div class="extra-small text-muted">Invite a new team member</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small { font-size: 0.75rem; }
    .btn-white { background: white; color: #374151; }
    .btn-white:hover { background: #f9fafb; border-color: #d1d5db; }
</style>
@endsection
