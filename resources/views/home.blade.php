@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1 class="content-title">Dashboard Overview</h1>
    </div>

    <div class="row g-4">
        <!-- Welcome Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-primary" style="border-left-color: #6c5ce7 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box p-2 rounded-3 bg-light text-primary">
                            <i class="fa-solid fa-user-circle fa-2x" style="color: #6c5ce7;"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Welcome Back</p>
                    <h4 class="fw-bold mb-0">{{ Auth::user()->name }}</h4>
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box p-2 rounded-3 bg-light text-success">
                            <i class="fa-solid fa-user-group fa-2x"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Total Users</p>
                    <h4 class="fw-bold mb-0">{{ $userCount }}</h4>
                </div>
            </div>
        </div>

        <!-- Total Banks Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box p-2 rounded-3 bg-light text-info">
                            <i class="fa-solid fa-building-columns fa-2x"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Total Banks</p>
                    <h4 class="fw-bold mb-0">{{ $bankCount }}</h4>
                </div>
            </div>
        </div>

        <!-- Total Cheques Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box p-2 rounded-3 bg-light text-warning">
                            <i class="fa-solid fa-money-check-dollar fa-2x"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Total Cheques</p>
                    <h4 class="fw-bold mb-0">{{ $chequeCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cheques -->
    <div class="card mt-4 border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Recent Cheques</h5>
            <a href="{{ route('cheques.index') }}" class="text-primary text-decoration-none small">View All</a>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="list-group list-group-flush">
                @forelse($recentCheques as $cheque)
                <div class="list-group-item px-0 py-3 border-0 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="status-dot rounded-circle" style="width: 8px; height: 8px; background-color: {{ $cheque->payment_status == 'paid' ? '#2ecc71' : '#f1c40f' }};"></div>
                        <div>
                            <p class="mb-0 fw-bold">{{ $cheque->cheque_number }} <span class="text-muted fw-normal">({{ $cheque->bank->name }})</span></p>
                            <span class="small text-muted">{{ $cheque->payer_name }} • {{ ucwords($cheque->payment_status) }}</span>
                        </div>
                    </div>
                    <span class="small text-muted">{{ $cheque->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">No recent cheques found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .border-primary { border-color: #6c5ce7 !important; }
    .bg-light-primary { background-color: #efedff !important; }
    .icon-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
    }
</style>
@endsection
