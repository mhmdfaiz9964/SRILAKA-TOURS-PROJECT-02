@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">System Management</h4>
            <p class="text-muted small mb-0">Monitor system health, updates and change logs</p>
        </div>
        <div class="d-flex gap-2">
            @can('system-manage')
            <form action="{{ route('system.storage-link') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-white btn-sm px-3 shadow-sm border-light d-flex align-items-center gap-2">
                    <i class="fa-solid fa-link text-black"></i> Storage Link
                </button>
            </form>
            <form action="{{ route('system.update') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Update System
                </button>
            </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 p-3 mb-4" style="background: #ecfdf5; color: #10b981;">
        <i class="fa-solid fa-circle-check fs-4"></i>
        <div>
            <div class="fw-bold">Success</div>
            <div class="small">{{ session('success') }}</div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <!-- System Info Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; background: #f5f3ff; color: #6366f1; font-size: 2rem;">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <h5 class="fw-bold mb-1">APEX CRM Engine</h5>
                    <p class="text-muted small mb-4">Production Version 2.0.1</p>
                    
                    <div class="d-flex flex-column gap-2 text-start">
                        <div class="p-2 px-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted">PHP Version</span>
                            <span class="small fw-bold">{{ PHP_VERSION }}</span>
                        </div>
                        <div class="p-2 px-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Laravel Version</span>
                            <span class="small fw-bold">{{ app()->version() }}</span>
                        </div>
                        <div class="p-2 px-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Environment</span>
                            <span class="small fw-bold text-success">Secure</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-terminal"></i> Terminal Status
                </h6>
                <div class="extra-small font-monospace opacity-75">
                    <div>> php artisan optimize:clear</div>
                    <div class="text-success">[DONE] Cache cleared</div>
                    <div>> php artisan migrate</div>
                    <div class="text-success">[DONE] Databases synced</div>
                    <div class="mt-2 text-warning animate-pulse">_ System Standing By</div>
                </div>
            </div>
        </div>

        <!-- Change Log Section -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">System Change Log History</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-container ps-3 border-start">
                        @forelse($logs as $log)
                        <div class="timeline-item position-relative mb-5 ps-4">
                            <div class="timeline-dot position-absolute rounded-circle" style="left: -9px; top: 0; width: 18px; height: 18px; background: #fff; border: 4px solid {{ $loop->first ? '#6366f1' : '#e2e8f0' }}; z-index: 1;"></div>
                            
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">{{ $log->title }} <span class="badge bg-light text-primary border ms-2" style="font-size: 0.65rem;">{{ $log->version }}</span></h6>
                                <span class="extra-small text-muted">{{ $log->created_at->format('M d, Y • H:i') }}</span>
                            </div>
                            <p class="small text-muted mb-0">{{ $log->description }}</p>
                            @if($loop->first)
                                <div class="mt-2">
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success py-1 px-3 border-0" style="font-size: 0.65rem;">Current Active Build</span>
                                </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="fa-solid fa-clock-rotate-left fs-1 text-light mb-3"></i>
                            <p class="text-muted small">No updates recorded yet.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small { font-size: 0.75rem; }
    .timeline-container { border-left: 2px solid #f1f5f9 !important; }
    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>
@endsection
