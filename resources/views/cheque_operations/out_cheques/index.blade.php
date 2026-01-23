@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Out Cheque Management</h4>
            <p class="text-muted small">Track all cheques issued from your accounts.</p>
        </div>
        <a href="{{ route('out-cheques.create') }}" class="btn btn-primary px-4 rounded-3 shadow-sm" style="background: #6366f1; border: none;">
            <i class="fa-solid fa-plus me-2"></i> Add New Out Cheque
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'All Out Cheques', 'key' => 'all', 'icon' => 'fa-list', 'color' => '#64748b', 'bg' => '#f8fafc', 'status' => ''],
                ['label' => 'Sent (Pending)', 'key' => 'sent', 'icon' => 'fa-paper-plane', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'status' => 'sent'],
                ['label' => 'Realized', 'key' => 'realized', 'icon' => 'fa-circle-check', 'color' => '#10b981', 'bg' => '#ecfdf5', 'status' => 'realized'],
                ['label' => 'Returned', 'key' => 'returned', 'icon' => 'fa-rotate-left', 'color' => '#ef4444', 'bg' => '#fef2f2', 'status' => 'returned'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('out-cheques.index', ['status' => $card['status']]) }}" class="text-decoration-none">
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
            <form action="{{ route('out-cheques.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Search</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="Name or #...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Payee Name</label>
                    <select name="payee_name" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Payees</option>
                        @foreach($payees as $payee)
                            <option value="{{ $payee }}" {{ request('payee_name') == $payee ? 'selected' : '' }}>{{ $payee }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Bank</label>
                    <select name="bank_id" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="realized" {{ request('status') == 'realized' ? 'selected' : '' }}>Realized</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Cheque Date</label>
                    <input type="date" name="cheque_date" value="{{ request('cheque_date') }}" class="form-control form-control-sm border-light rounded-3">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('out-cheques.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $cheques->total() }} Results</div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3 text-muted small text-uppercase">Type</th>
                        <th class="py-3 text-muted small text-uppercase">Cheq Date</th>
                        <th class="py-3 text-muted small text-uppercase">Cheq #</th>
                        <th class="py-3 text-muted small text-uppercase">Bank</th>
                        <th class="py-3 text-muted small text-uppercase">Payee Name</th>
                        <th class="py-3 text-muted small text-uppercase text-end">Amount</th>
                        <th class="py-3 text-muted small text-uppercase text-center">Status</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    <tr>
                        <td class="ps-4">
                            <span class="badge rounded-pill px-2 py-1" style="background: #fff7ed; color: #f97316; font-size: 0.65rem;">
                                OUT
                            </span>
                        </td>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
                        <td class="small fw-bold">#{{ $cheque->cheque_number }}</td>
                        <td class="small">{{ $cheque->bank->name }}</td>
                        <td class="small fw-bold text-dark">{{ $cheque->payee_name }}</td>
                        <td class="small fw-bold text-end">LKR {{ number_format($cheque->amount, 2) }}</td>
                        <td class="text-center">
                            @php
                                $statusColors = [
                                    'sent' => ['bg' => '#fffbeb', 'text' => '#f59e0b', 'label' => 'Sent'],
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
                                <a href="{{ route('out-cheques.edit', $cheque) }}" class="btn btn-sm btn-icon border-0 text-dark shadow-none">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('out-cheques.destroy', $cheque) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
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
                        <td colspan="8" class="text-center py-5 text-muted small">No records found.</td>
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
    .extra-small { font-size: 0.7rem; }
</style>
@endsection
