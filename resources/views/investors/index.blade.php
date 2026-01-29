@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">Investor Management</h4>
                <span class="badge bg-light text-muted border px-2 py-1 ms-2" style="font-size: 0.65rem;">Active Records</span>
            </div>
            
            <!-- Summary Balance Card -->
            <div class="d-flex align-items-center bg-white border border-primary border-opacity-25 px-3 py-2 rounded-4 shadow-sm ms-3" style="background: #f5f3ff !important; min-width: 200px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: #6366f1; color: #fff;">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 1.1rem; color: #111827; line-height: 1;">Total Invest</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="fw-bold text-primary" style="font-size: 0.9rem;">LKR {{ number_format($total_invested ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center bg-white border border-warning border-opacity-25 px-3 py-2 rounded-4 shadow-sm ms-3" style="background: #fffbeb !important; min-width: 200px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: #f59e0b; color: #fff;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 1.1rem; color: #111827; line-height: 1;">Pending Invest</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="fw-bold text-warning" style="font-size: 0.9rem;">LKR {{ number_format($active_investment ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center bg-white border border-success border-opacity-25 px-3 py-2 rounded-4 shadow-sm ms-3" style="background: #ecfdf5 !important; min-width: 200px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: #10b981; color: #fff;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 1.1rem; color: #111827; line-height: 1;">Total Paid Profit</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                         <span class="fw-bold text-success" style="font-size: 0.9rem;">LKR {{ number_format($total_paid_profit ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
    <div class="d-flex align-items-center gap-3">
        <form action="{{ route('investors.index') }}" method="GET" class="search-box position-relative d-flex gap-2">
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-5 border-0 shadow-sm rounded-pill" style="width: 350px; background: #fff;" placeholder="Search by investor name...">
            </div>
            
            <select class="form-select border-0 shadow-sm rounded-pill" style="width: 180px;" name="sort" onchange="this.form.submit()">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="highest_amount" {{ request('sort') == 'highest_amount' ? 'selected' : '' }}>Highest Amount</option>
                <option value="lowest_amount" {{ request('sort') == 'lowest_amount' ? 'selected' : '' }}>Lowest Amount</option>
                <option value="name_az" {{ request('sort') == 'name_az' ? 'selected' : '' }}>Name (A-Z)</option>
             </select>
        </form>
    </div>
</div>

    <!-- Toolbar Section -->
    <div class="toolbar d-flex align-items-center justify-content-between mb-3 bg-white p-2 rounded-4 shadow-sm border border-light">
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.location.reload()" class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-rotate text-black"></i> Refresh
            </button>
            
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-filter text-black"></i> Date Filter
                </button>
                <div class="dropdown-menu p-4 shadow-lg border-0 rounded-4" style="width: 350px;">
                    <form action="{{ route('investors.index') }}" method="GET">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted text-uppercase text-nowrap">From Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm border-light bg-light rounded-3" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted text-uppercase text-nowrap">To Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm border-light bg-light rounded-3" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-3" style="background: #6366f1; border: none;">Apply</button>
                            <a href="{{ route('investors.index') }}" class="btn btn-light btn-sm border-light rounded-3">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-2 px-3 small fw-bold text-muted border-start ms-2">{{ $investors->total() }} Investors</div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @can('investor-create')
            <a href="{{ route('investors.create') }}" class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                <i class="fa-solid fa-plus"></i> Add Investor
            </a>
            @endcan
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle shadow-sm" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-file-export text-black"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 180px;">
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3" href="{{ route('investors.export', array_merge(request()->all(), ['format' => 'excel'])) }}">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-excel text-success"></i>
                        </div>
                        <span class="small fw-bold">Excel Format</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1" href="{{ route('investors.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
                        <div class="bg-danger bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-pdf text-danger"></i>
                        </div>
                        <span class="small fw-bold">PDF Format</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3 text-muted small text-uppercase">Status</th>
                        <th class="py-3 text-muted small text-uppercase">Investor Name</th>
                        <th class="py-3 text-muted small text-uppercase">Collect Date</th>
                        <th class="py-3 text-muted small text-uppercase">Refund Date</th>
                        <th class="py-3 text-muted small text-uppercase">Duration</th>
                        <th class="py-3 text-muted small text-uppercase">Invested</th>
                        <th class="py-3 text-muted small text-uppercase">Exp. Profit</th>
                        <th class="py-3 text-muted small text-uppercase">Paid Profit</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investors as $investor)
                    <tr>
                         <td class="ps-4">
                            @if($investor->status == 'active')
                                <span class="badge bg-warning-subtle text-warning border border-0 px-2 py-1 rounded-pill">Active</span>
                            @elseif($investor->status == 'paid')
                                <span class="badge bg-success-subtle text-success border border-0 px-2 py-1 rounded-pill">Paid</span>
                            @elseif($investor->status == 'pending')
                                <span class="badge bg-info-subtle text-info border border-0 px-2 py-1 rounded-pill">Pending</span>
                            @elseif($investor->status == 'waiting')
                                <span class="badge bg-secondary-subtle text-secondary border border-0 px-2 py-1 rounded-pill">Waiting</span>
                            @else
                                <span class="badge bg-light text-muted border border-0 px-2 py-1 rounded-pill">{{ ucfirst($investor->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $investor->name }}</div>
                        </td>
                         <td class="small text-muted">{{ $investor->collect_date ? \Carbon\Carbon::parse($investor->collect_date)->format('d/m/Y') : '-' }}</td>
                        <td class="small text-muted">{{ $investor->refund_date ? \Carbon\Carbon::parse($investor->refund_date)->format('d/m/Y') : '-' }}</td>
                        <td class="small fw-bold text-primary">
                            @if($investor->collect_date && $investor->refund_date)
                                {{ \Carbon\Carbon::parse($investor->collect_date)->diffInDays(\Carbon\Carbon::parse($investor->refund_date)) }} Days
                            @else
                                -
                            @endif
                        </td>
                        <td class="small fw-bold text-nowrap">LKR {{ number_format($investor->invest_amount, 2) }}</td>
                        <td class="small text-success fw-bold text-nowrap">LKR {{ number_format($investor->expect_profit, 2) }}</td>
                        <td class="small text-primary fw-bold text-nowrap">
                            LKR {{ number_format($investor->paid_profit, 2) }}
                            @if($investor->invest_amount > 0)
                                <small class="text-muted ms-1">({{ round(($investor->paid_profit / $investor->invest_amount) * 100, 1) }}%)</small>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                @can('investor-edit')
                                <a href="{{ route('investors.edit', $investor) }}" class="btn btn-sm btn-icon border-0 text-black shadow-none">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endcan
                                @can('investor-delete')
                                <button type="button" class="btn btn-sm btn-icon border-0 text-black shadow-none" 
                                        onclick="confirmDelete({{ $investor->id }}, 'delete-investor-{{ $investor->id }}')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <form id="delete-investor-{{ $investor->id }}" action="{{ route('investors.destroy', $investor) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">No investors found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between p-4 border-top">
            <div class="text-muted small">Showing {{ $investors->count() }} of {{ $investors->total() }} results</div>
            <div class="pagination-custom d-flex gap-2">
                {{ $investors->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .btn-icon:hover {
        background: #f1f5f9;
        color: #6366f1 !important;
        border-radius: 8px;
    }
    .btn-white { background: #fff; border: 1px solid #f1f5f9; color: #475569; border-radius: 10px; font-size: 0.85rem; }
    .btn-white:hover { background: #f8fafc; }
    .table th { background: transparent; border-bottom: none; font-size: 0.7rem; letter-spacing: 0.05em; font-weight: 700; }
    .table td { border-bottom: 1px solid #f8fafc; height: 60px; }
    .toolbar .dropdown:hover .dropdown-menu { display: block; margin-top: 0; }
</style>

<script>
function confirmDelete(id, formId) {
    Swal.fire({
        title: 'Delete Investor?',
        text: "Are you sure you want to remove this investor?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    })
}
</script>
@endsection
