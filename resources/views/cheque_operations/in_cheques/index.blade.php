@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">In Cheque Management</h4>
            <p class="text-muted small">Manage all incoming cheques from clients.</p>
        </div>
        <a href="{{ route('in-cheques.create') }}" class="btn btn-primary px-4 rounded-3 shadow-sm" style="background: #6366f1; border: none;">
            <i class="fa-solid fa-plus me-2"></i> Add New In Cheque
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'All In Cheques', 'value' => $stats['all'], 'icon' => 'fa-list', 'color' => '#64748b', 'bg' => '#f8fafc', 'status' => ''],
                ['label' => 'In Hand', 'value' => $stats['in_hand'], 'icon' => 'fa-hand-holding-dollar', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'status' => 'received'],
                ['label' => 'Deposited', 'value' => $stats['deposited'], 'icon' => 'fa-building-columns', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'status' => 'deposited'],
                ['label' => 'Transferred', 'value' => $stats['transferred'], 'icon' => 'fa-right-left', 'color' => '#8b5cf6', 'bg' => '#f5f3ff', 'status' => 'transferred_to_third_party'],
                ['label' => 'Returned', 'value' => $stats['returned'], 'icon' => 'fa-rotate-left', 'color' => '#ef4444', 'bg' => '#fef2f2', 'status' => 'returned'],
                ['label' => 'Realized', 'value' => $stats['realized'], 'icon' => 'fa-circle-check', 'color' => '#10b981', 'bg' => '#ecfdf5', 'status' => 'realized'],
                ['label' => 'Deposit Today', 'value' => $stats['to_deposit_today'], 'icon' => 'fa-calendar-day', 'color' => '#06b6d4', 'bg' => '#ecfeff', 'status' => 'today'],
                ['label' => 'Overdue', 'value' => $stats['overdue'], 'icon' => 'fa-clock', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'status' => 'overdue'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('in-cheques.index', ['status' => $card['status']]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden card-stat" style="background: {{ $card['bg'] }};">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">{{ $card['label'] }}</div>
                                <div class="h4 fw-bold mb-0" style="color: {{ $card['color'] }};">{{ $card['value'] }}</div>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; background: #fff; color: {{ $card['color'] }};">
                                <i class="fa-solid {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Table Section -->
    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-10">
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('in-cheques.index') }}" method="GET" class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" style="width: 250px;" placeholder="Search by name or #...">
                </form>
            </div>
            <div class="p-2 px-3 small fw-bold text-muted border-start ms-2">{{ $cheques->total() }} Results</div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3 text-muted small text-uppercase">Cheq Date</th>
                        <th class="py-3 text-muted small text-uppercase">Cheq #</th>
                        <th class="py-3 text-muted small text-uppercase">Bank</th>
                        <th class="py-3 text-muted small text-uppercase">Payer Name</th>
                        <th class="py-3 text-muted small text-uppercase text-end">Amount</th>
                        <th class="py-3 text-muted small text-uppercase text-center">Status</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    <tr>
                        <td class="ps-4 small text-muted">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
                        <td class="small fw-bold">#{{ $cheque->cheque_number }}</td>
                        <td class="small">{{ $cheque->bank->name }}</td>
                        <td class="small fw-bold text-dark">{{ $cheque->payer_name }}</td>
                        <td class="small fw-bold text-end">LKR {{ number_format($cheque->amount, 2) }}</td>
                        <td class="text-center">
                            @php
                                $statusColors = [
                                    'received' => ['bg' => '#fffbeb', 'text' => '#f59e0b', 'label' => 'Hand'],
                                    'deposited' => ['bg' => '#eff6ff', 'text' => '#3b82f6', 'label' => 'Deposited'],
                                    'transferred_to_third_party' => ['bg' => '#f5f3ff', 'text' => '#8b5cf6', 'label' => 'Transferred'],
                                    'realized' => ['bg' => '#ecfdf5', 'text' => '#10b981', 'label' => 'Realized'],
                                    'returned' => ['bg' => '#fef2f2', 'text' => '#ef4444', 'label' => 'Returned'],
                                ];
                                $st = $statusColors[$cheque->status] ?? ['bg' => '#eee', 'text' => '#666', 'label' => $cheque->status];
                            @endphp
                            <span class="badge rounded-pill px-2 py-1" style="background: {{ $st['bg'] }}; color: {{ $st['text'] }}; font-size: 0.65rem;">
                                {{ $st['label'] }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('in-cheques.edit', $in_cheque = $cheque) }}" class="btn btn-sm btn-icon border-0 text-dark shadow-none">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('in-cheques.destroy', $cheque) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon border-0 text-danger shadow-none">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted small">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-top">
            {{ $cheques->links() }}
        </div>
    </div>
</div>

<style>
    .card-stat { transition: all 0.2s ease-in-out; border: 1px solid transparent !important; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: rgba(99, 102, 241, 0.2) !important; }
    .btn-icon:hover { background: #f1f5f9; border-radius: 8px; }
</style>
@endsection
