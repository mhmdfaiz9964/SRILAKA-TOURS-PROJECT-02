@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Investor Management</h4>
            <p class="text-muted small">Manage all investment records and profit tracking.</p>
        </div>
        @can('investor-create')
        <a href="{{ route('investors.create') }}" class="btn btn-primary px-4 rounded-3 shadow-sm" style="background: #6366f1; border: none;">
            <i class="fa-solid fa-plus me-2"></i> Add New Investor
        </a>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'All Investors', 'key' => 'all', 'icon' => 'fa-users', 'color' => '#64748b', 'bg' => '#f8fafc', 'status' => ''],
                ['label' => 'Active Invest', 'key' => 'active', 'icon' => 'fa-hand-holding-dollar', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'status' => 'active'],
                ['label' => 'Paid Invest', 'key' => 'paid', 'icon' => 'fa-circle-check', 'color' => '#10b981', 'bg' => '#ecfdf5', 'status' => 'paid'],
                ['label' => 'Waiting Invest', 'key' => 'waiting', 'icon' => 'fa-clock', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'status' => 'waiting'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('investors.index', ['status' => $card['status']]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden card-stat" style="background: {{ $card['bg'] }};">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">{{ $card['label'] }}</div>
                                <div class="h4 fw-bold mb-0" style="color: {{ $card['color'] }};">{{ $stats[$card['key']]['count'] }}</div>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; background: #fff; color: {{ $card['color'] }};">
                                <i class="fa-solid {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: {{ $card['color'] }}33 !important;">
                            <span class="extra-small text-muted fw-bold">Total Amount</span>
                            <span class="small fw-bold" style="color: {{ $card['color'] }};">LKR {{ number_format($stats[$card['key']]['amount'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Table Section -->
    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ route('investors.index') }}" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search Investor</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="Name...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Sort By</label>
                    <select class="form-select form-select-sm border-light rounded-3" name="sort">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="highest_amount" {{ request('sort') == 'highest_amount' ? 'selected' : '' }}>Highest Amount</option>
                        <option value="lowest_amount" {{ request('sort') == 'lowest_amount' ? 'selected' : '' }}>Lowest Amount</option>
                        <option value="name_az" {{ request('sort') == 'name_az' ? 'selected' : '' }}>Name (A-Z)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Collect Date Range</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control border-light rounded-3" value="{{ request('start_date') }}">
                        <span class="input-group-text bg-transparent border-0 text-muted">-</span>
                        <input type="date" name="end_date" class="form-control border-light rounded-3" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('investors.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                        <div class="dropdown ms-auto">
                            <button class="btn btn-light btn-sm px-3 border-light rounded-3 dropdown-toggle shadow-none" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-file-export me-1"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
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
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $investors->total() }} Results</div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3 text-muted small text-uppercase">Status</th>
                        <th class="py-3 text-muted small text-uppercase">Investor Name</th>
                        <th class="py-3 text-muted small text-uppercase text-center">Collect Date</th>
                        <th class="py-3 text-muted small text-uppercase text-center">Refund Date</th>
                        <th class="py-3 text-muted small text-uppercase text-center">Duration</th>
                        <th class="py-3 text-muted small text-uppercase">Invested Amount</th>
                        <th class="py-3 text-muted small text-uppercase">Exp. Profit</th>
                        <th class="py-3 text-muted small text-uppercase">Paid Profit</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investors as $investor)
                    <tr>
                        <td class="ps-4">
                            @php
                                $statusMeta = [
                                    'active' => ['bg' => '#fffbeb', 'text' => '#f59e0b', 'label' => 'Active'],
                                    'paid' => ['bg' => '#ecfdf5', 'text' => '#10b981', 'label' => 'Paid'],
                                    'pending' => ['bg' => '#eff6ff', 'text' => '#3b82f6', 'label' => 'Pending'],
                                    'waiting' => ['bg' => '#f5f3ff', 'text' => '#8b5cf6', 'label' => 'Waiting'],
                                ];
                                $st = $statusMeta[$investor->status] ?? ['bg' => '#f1f5f9', 'text' => '#64748b', 'label' => ucfirst($investor->status)];
                            @endphp
                            <span class="badge rounded-pill px-2 py-1" style="background: {{ $st['bg'] }}; color: {{ $st['text'] }}; font-size: 0.65rem;">
                                {{ $st['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $investor->name }}</div>
                        </td>
                        <td class="small text-muted text-center">{{ $investor->collect_date ? \Carbon\Carbon::parse($investor->collect_date)->format('d/m/Y') : '-' }}</td>
                        <td class="small text-muted text-center">{{ $investor->refund_date ? \Carbon\Carbon::parse($investor->refund_date)->format('d/m/Y') : '-' }}</td>
                        <td class="small fw-bold text-primary text-center">
                            @if($investor->collect_date && $investor->refund_date)
                                {{ \Carbon\Carbon::parse($investor->collect_date)->diffInDays(\Carbon\Carbon::parse($investor->refund_date)) }} Days
                            @else
                                -
                            @endif
                        </td>
                        <td class="small fw-bold">LKR {{ number_format($investor->invest_amount, 2) }}</td>
                        <td class="small text-success fw-bold">LKR {{ number_format($investor->expect_profit, 2) }}</td>
                        <td class="small text-primary fw-bold">
                            LKR {{ number_format($investor->paid_profit, 2) }}
                            @if($investor->invest_amount > 0)
                                <small class="text-muted ms-1" style="font-size: 0.6rem;">({{ round(($investor->paid_profit / $investor->invest_amount) * 100, 1) }}%)</small>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                @can('investor-edit')
                                <a href="{{ route('investors.edit', $investor) }}" class="btn btn-sm btn-icon border-0 text-dark shadow-none">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endcan
                                @can('investor-delete')
                                <button type="button" class="btn btn-sm btn-icon border-0 text-danger shadow-none" 
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
                        <td colspan="9" class="text-center py-5 text-muted small">No investors found.</td>
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
    .card-stat { transition: all 0.2s ease-in-out; border: 1px solid transparent !important; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: rgba(99, 102, 241, 0.2) !important; }
    .btn-icon:hover { background: #f1f5f9; border-radius: 8px; }
</style>

<script>
function confirmDelete(id, formId) {
    Swal.fire({
        title: 'Delete Investor?',
        text: "Are you sure you want to remove this investor record?",
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
