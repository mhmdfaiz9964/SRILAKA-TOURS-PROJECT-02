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
                        <i class="fa-solid fa-edit text-muted"></i>
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
                    <h4 class="fw-bold mb-0">5</h4>
                </div>
            </div>
        </div>

        <!-- Total Datas Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box p-2 rounded-3 bg-light text-info">
                            <i class="fa-solid fa-database fa-2x"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Total Datas</p>
                    <h4 class="fw-bold mb-0">20</h4>
                </div>
            </div>
        </div>

        <!-- Last Login Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box p-2 rounded-3 bg-light text-warning">
                            <i class="fa-solid fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">Last Login</p>
                    <h4 class="fw-bold mb-0">12 minutes ago</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="row g-4 mt-2">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0">Application Status</h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <!-- Placeholder for Chart -->
                    <div style="width: 200px; height: 200px; border-radius: 50%; border: 30px solid #eee; position: relative;">
                        <div style="position: absolute; top: -30px; left: -30px; width: 200px; height: 200px; border-radius: 50%; border: 30px solid transparent; border-top-color: #3498db; transform: rotate(45deg);"></div>
                        <div style="position: absolute; top: -30px; left: -30px; width: 200px; height: 200px; border-radius: 50%; border: 30px solid transparent; border-right-color: #2ecc71; transform: rotate(140deg);"></div>
                        <div style="position: absolute; top: -30px; left: -30px; width: 200px; height: 200px; border-radius: 50%; border: 30px solid transparent; border-bottom-color: #f1c40f; transform: rotate(-30deg);"></div>
                    </div>
                    <div class="mt-4 d-flex flex-wrap justify-content-center gap-3">
                        <div class="small"><i class="fa-solid fa-circle text-primary me-1"></i> New</div>
                        <div class="small"><i class="fa-solid fa-circle text-warning me-1"></i> Process</div>
                        <div class="small"><i class="fa-solid fa-circle text-success me-1"></i> Approved</div>
                        <div class="small"><i class="fa-solid fa-circle text-danger me-1"></i> Rejected</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0">Applications Last 7 Days</h6>
                    <select class="form-select form-select-sm w-auto">
                        <option>All Users</option>
                    </select>
                </div>
                <div class="card-body p-0 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                    <p class="text-muted">Chart visualization area</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="card mt-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Recent Applications</h5>
            <a href="#" class="text-primary text-decoration-none small">View All</a>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="list-group list-group-flush">
                <div class="list-group-item px-0 py-3 border-0 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="status-dot rounded-circle" style="width: 8px; height: 8px; background-color: #2ecc71;"></div>
                        <div>
                            <p class="mb-0 fw-bold">Sulojan <span class="text-muted fw-normal">(N099123)</span></p>
                            <span class="small text-muted">Oman • Approved</span>
                        </div>
                    </div>
                    <span class="small text-muted">3 weeks ago</span>
                </div>
                <div class="list-group-item px-0 py-3 border-0 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="status-dot rounded-circle" style="width: 8px; height: 8px; background-color: #2ecc71;"></div>
                        <div>
                            <p class="mb-0 fw-bold">Vijayrathnam <span class="text-muted fw-normal">(P885214)</span></p>
                            <span class="small text-muted">Oman • Approved</span>
                        </div>
                    </div>
                    <span class="small text-muted">3 weeks ago</span>
                </div>
                <div class="list-group-item px-0 py-3 border-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="status-dot rounded-circle" style="width: 8px; height: 8px; background-color: #2ecc71;"></div>
                        <div>
                            <p class="mb-0 fw-bold">Niluja <span class="text-muted fw-normal">(N088526)</span></p>
                            <span class="small text-muted">Oman • Approved</span>
                        </div>
                    </div>
                    <span class="small text-muted">3 weeks ago</span>
                </div>
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
