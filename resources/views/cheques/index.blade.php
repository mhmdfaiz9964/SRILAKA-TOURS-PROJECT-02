@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">Cheque Management</h4>
            <span class="badge bg-light text-muted border px-2 py-1 ms-2" style="font-size: 0.65rem;">{{ $page_title ?? 'Active' }}</span>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <form action="{{ url()->current() }}" method="GET" class="search-box position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-5 border-0 shadow-sm rounded-pill" style="width: 350px; background: #fff;" placeholder="Search by name or number...">
                @foreach(request()->except('search', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
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
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-filter text-black"></i> Filter {{ request()->hasAny(['bank_id', 'payer_name', 'third_party', 'start_date']) ? '(Active)' : '' }}
                </button>
                <div class="dropdown-menu p-4 shadow-lg border-0 rounded-4" style="width: 350px;">
                    <form action="{{ url()->current() }}" method="GET">
                        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Bank</label>
                            <select name="bank_id" class="form-select form-select-sm border-light bg-light rounded-3">
                                <option value="">All Banks</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payer</label>
                            <select name="payer_name" class="form-select form-select-sm border-light bg-light rounded-3">
                                <option value="">All Payers</option>
                                @foreach($payers as $payer)
                                    <option value="{{ $payer }}" {{ request('payer_name') == $payer ? 'selected' : '' }}>{{ $payer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">3rd Part</label>
                            <select name="third_party" class="form-select form-select-sm border-light bg-light rounded-3">
                                <option value="">All 3rd Parties</option>
                                @foreach($third_parties as $tp)
                                    <option value="{{ $tp }}" {{ request('third_party') == $tp ? 'selected' : '' }}>{{ $tp }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-3" style="background: #6366f1; border: none;">Apply Filters</button>
                            <a href="{{ url()->current() }}" class="btn btn-light btn-sm border-light rounded-3">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-arrow-down-short-wide text-black"></i> 
                    @php
                        $sorts = [
                            'latest' => 'Latest First',
                            'oldest' => 'Oldest First',
                            'amount_high' => 'Highest Amount',
                            'amount_low' => 'Lowest Amount',
                            'name_asc' => 'Name (A-Z)'
                        ];
                        echo $sorts[request('sort', 'latest')] ?? 'Sort';
                    @endphp
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                    @foreach($sorts as $key => $label)
                        <li><a class="dropdown-item small {{ request('sort', 'latest') == $key ? 'bg-light fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="p-2 px-3 small fw-bold text-muted border-start ms-2">{{ $cheques->total() }} Results</div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('cheques.create') }}" class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                <i class="fa-solid fa-plus"></i> Add New
            </a>
            <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-upload text-black"></i> Import/Export
            </button>
            <a href="{{ url()->current() }}" class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-rotate-left text-black"></i> Reset
            </a>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input shadow-none">
                        </th>
                        <th class="py-3" style="width: 40px;"></th>
                        <th class="py-3 text-muted small text-uppercase">Client Name</th>
                        <th class="py-3 text-muted small text-uppercase">Bank</th>
                        <th class="py-3 text-muted small text-uppercase">Amount (LKR)</th>
                        <th class="py-3 text-muted small text-uppercase">Balance</th>
                        <th class="py-3 text-muted small text-uppercase">3rd Part</th>
                        <th class="py-3 text-muted small text-uppercase">3rd Part Status</th>
                        <th class="py-3 text-muted small text-uppercase">Status</th>
                        <th class="py-3 text-muted small text-uppercase">Date</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    <tr>
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input shadow-none">
                        </td>
                        <td>
                            <i class="fa-regular fa-star text-muted cursor-pointer"></i>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="background: #8b5cf6; width: 30px; height: 30px; font-size: 0.75rem;">
                                    {{ substr($cheque->payer_name, 0, 1) }}
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('cheques.show', $cheque) }}" class="fw-bold text-dark small text-decoration-none hover-underline">{{ $cheque->payer_name }}</a>
                                    <span class="text-muted" style="font-size: 0.65rem;">#{{ $cheque->cheque_number }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="small">{{ $cheque->bank->name }}</td>
                        <td class="small fw-bold">LKR {{ number_format($cheque->amount, 2) }}</td>
                        <td class="small fw-bold text-danger">LKR {{ number_format($cheque->amount - ($cheque->payments_sum_amount ?? 0), 2) }}</td>
                        <td class="small">{{ $cheque->payee_name ?? '-' }}</td>
                        <td>
                            @if($cheque->payee_name)
                                @php
                                    $tpStatusColor = $cheque->third_party_payment_status == 'paid' ? '#10b981' : '#f97316';
                                    $tpStatusBg = $cheque->third_party_payment_status == 'paid' ? '#ecfdf5' : '#fff7ed';
                                @endphp
                                <div class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded-3" style="background: {{ $tpStatusBg }};">
                                    <span class="rounded-circle" style="width: 6px; height: 6px; background: {{ $tpStatusColor }};"></span>
                                    <span class="small fw-medium" style="color: {{ $tpStatusColor }}; font-size: 0.7rem;">{{ ucwords($cheque->third_party_payment_status) }}</span>
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusMeta = match($cheque->payment_status) {
                                    'paid' => ['color' => '#10b981', 'bg' => '#ecfdf5', 'text' => 'Paid'],
                                    'partial paid' => ['color' => '#f59e0b', 'bg' => '#fffbeb', 'text' => 'Partial'],
                                    default => ['color' => '#ef4444', 'bg' => '#fef2f2', 'text' => 'Pending']
                                };
                            @endphp
                            <div class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded-3" style="background: {{ $statusMeta['bg'] }};">
                                <span class="rounded-circle" style="width: 6px; height: 6px; background: {{ $statusMeta['color'] }};"></span>
                                <span class="small fw-medium" style="color: {{ $statusMeta['color'] }}; font-size: 0.7rem;">{{ $statusMeta['text'] }}</span>
                            </div>
                        </td>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('cheques.show', $cheque) }}" class="btn btn-sm btn-icon border-0 text-black shadow-none">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('cheques.edit', $cheque) }}" class="btn btn-sm btn-icon border-0 text-black shadow-none">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-icon border-0 text-black shadow-none" 
                                        onclick="confirmDelete({{ $cheque->id }}, 'delete-cheque-{{ $cheque->id }}')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <form id="delete-cheque-{{ $cheque->id }}" action="{{ route('cheques.destroy', $cheque) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted small">No cheques found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between p-4 border-top">
            <div class="text-muted small">1-{{ $cheques->count() }} of {{ $cheques->total() }}</div>
            <div class="pagination-custom d-flex gap-2">
                {{ $cheques->links() }}
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Row/Page:</span>
                <select class="form-select form-select-sm border-0 bg-light shadow-none" style="width: 70px;">
                    <option>10</option>
                    <option>20</option>
                    <option>50</option>
                </select>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #fcfcfd; }
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
        color: #4a5568;
        position: relative;
    }
    .divider { height: 20px; width: 1px; background: #e2e8f0; }
    .btn-icon:hover {
        background: #f1f5f9;
        color: #6366f1 !important;
        border-radius: 8px;
    }
    .btn-white { background: #fff; border: 1px solid #f1f5f9; color: #475569; border-radius: 10px; font-size: 0.85rem; }
    .btn-white:hover { background: #f8fafc; }
    .table th { background: transparent; border-bottom: none; font-size: 0.7rem; letter-spacing: 0.05em; font-weight: 700; }
    .table td { border-bottom: 1px solid #f8fafc; height: 65px; }
    .form-check-input:checked { background-color: #6366f1; border-color: #6366f1; }
    .pagination-custom .pagination { margin-bottom: 0; }
    .pagination-custom .page-link { border: none; background: transparent; color: #64748b; font-size: 0.85rem; margin: 0 2px; }
    .pagination-custom .page-item.active .page-link { color: #6366f1; font-weight: bold; background: #f5f3ff; border-radius: 8px; }
    .avatar-sm { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .dropdown-item:active { background-color: #6366f1; }
</style>
@endsection
