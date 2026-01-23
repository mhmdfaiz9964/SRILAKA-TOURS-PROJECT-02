@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Business partner</span> <span class="fw-bold fs-5">APEX CRM</span>
            <span class="badge bg-light text-muted border px-2 py-1 ms-2" style="font-size: 0.65rem;">New Data</span>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="search-box position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                <input type="text" id="globalSearch" class="form-control ps-5 border-0 shadow-sm rounded-pill" style="width: 350px; background: #fff;" placeholder="Search">
            </div>
            <div class="user-stack d-flex align-items-center">
                <div class="avatar-group d-flex me-3">
                    <div class="avatar-circle">M</div>
                    <div class="avatar-circle" style="left: -10px;">A</div>
                    <div class="avatar-circle" style="left: -20px;">+4</div>
                </div>
                <button class="btn btn-white btn-sm px-3 fw-bold">Add</button>
                <div class="divider mx-3"></div>
                <button class="btn btn-white btn-sm px-3"><i class="fa-solid fa-share-nodes me-2"></i>Share</button>
                <button class="btn btn-white btn-sm px-2 ms-2"><i class="fa-solid fa-square"></i></button>
            </div>
        </div>
    </div>

    <!-- Toolbar Section -->
    <div class="toolbar d-flex align-items-center justify-content-between mb-3 bg-white p-2 rounded-4 shadow-sm border border-light">
        <div class="d-flex align-items-center gap-2">
            <button onclick="updateSystem()" class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-rotate text-black"></i> Update
            </button>
            <div class="p-2 px-3 bg-light rounded-3 small fw-bold text-muted">0 Selected</div>
            
            <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-filter text-black"></i> Filter 0
            </button>
            
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-arrow-down-short-wide text-black"></i> Short
                </button>
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
            <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-eye text-black"></i> View
            </button>
            <button class="btn btn-white btn-sm px-2 border-light rounded-3 ml-2">
                <i class="fa-solid fa-ellipsis-vertical text-black"></i>
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input">
                        </th>
                        <th class="py-3" style="width: 40px;"></th>
                        <th class="py-3 text-muted small text-uppercase">Client Name</th>
                        <th class="py-3 text-muted small text-uppercase">Bank</th>
                        <th class="py-3 text-muted small text-uppercase">Amount (LKR)</th>
                        <th class="py-3 text-muted small text-uppercase">3rd Part</th>
                        <th class="py-3 text-muted small text-uppercase">Status</th>
                        <th class="py-3 text-muted small text-uppercase">Date</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Categories</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    <tr>
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input">
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
                        <td class="small">{{ $cheque->payee_name ?? '-' }}</td>
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
                                <span class="badge bg-light text-muted fw-normal" style="font-size: 0.65rem;">Financing</span>
                                <span class="badge bg-light text-muted fw-normal" style="font-size: 0.65rem;">B2B</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted small">No cheques found matching your criteria.</td>
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
                <select class="form-select form-select-sm border-0 bg-light" style="width: 70px;">
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
    .btn-white { background: #fff; border: 1px solid #f1f5f9; color: #475569; border-radius: 10px; font-size: 0.85rem; }
    .btn-white:hover { background: #f8fafc; }
    .table th { background: transparent; border-bottom: none; font-size: 0.7rem; letter-spacing: 0.05em; font-weight: 700; }
    .table td { border-bottom: 1px solid #f8fafc; height: 65px; }
    .form-check-input:checked { background-color: #6366f1; border-color: #6366f1; }
    .pagination-custom .pagination { margin-bottom: 0; }
    .pagination-custom .page-link { border: none; background: transparent; color: #64748b; font-size: 0.85rem; margin: 0 2px; }
    .pagination-custom .page-item.active .page-link { color: #6366f1; font-weight: bold; background: #f5f3ff; border-radius: 8px; }
    .avatar-sm { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>
@endsection
