@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Header -->
    <!-- Breadcrumb & Top Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">Cheque Management</h4>
                <span class="badge bg-light text-muted border px-2 py-1 ms-2" style="font-size: 0.65rem;">{{ $page_title ?? 'Active' }}</span>
            </div>
            
            <!-- Summary Balance Card -->
            <div class="d-flex align-items-center bg-white border border-success border-opacity-25 px-3 py-2 rounded-4 shadow-sm ms-3" style="background: #f0fdf4 !important; min-width: 250px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: #10b981; color: #fff;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 1.1rem; color: #111827; line-height: 1;">Total Balance</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="text-muted" style="font-size: 0.75rem;">Total Remaining:</span>
                        <span class="fw-bold text-success" style="font-size: 0.9rem;">LKR {{ number_format($total_balance ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2">
             @can('cheque-create')
            <a href="{{ route('cheques.create') }}" class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
                <i class="fa-solid fa-plus"></i> Add New
            </a>
            @endcan
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle shadow-sm" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-file-export text-black"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 180px;">
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3" href="{{ route('cheques.export', array_merge(request()->all(), ['format' => 'excel', 'view' => ($page_title ?? '') == 'Payment Cheques' ? 'payment' : (($page_title ?? '') == 'Paid Cheques' ? 'paid' : 'index')])) }}">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-excel text-success"></i>
                        </div>
                        <span class="small fw-bold">Excel Format</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1" href="{{ route('cheques.export', array_merge(request()->all(), ['format' => 'pdf', 'view' => ($page_title ?? '') == 'Payment Cheques' ? 'payment' : (($page_title ?? '') == 'Paid Cheques' ? 'paid' : 'index')])) }}">
                        <div class="bg-danger bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-pdf text-danger"></i>
                        </div>
                        <span class="small fw-bold">PDF Format</span>
                    </a>
                </div>
            </div>
        </div>
    </div>



    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="Name or number...">
                    </div>
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
                    <label class="form-label small fw-bold text-muted mb-1">Payer</label>
                    <select name="payer_name" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Payers</option>
                        @foreach($payers as $payer)
                            <option value="{{ $payer }}" {{ request('payer_name') == $payer ? 'selected' : '' }}>{{ $payer }}</option>
                        @endforeach
                    </select>
                </div>
                 <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Date Range</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control border-light rounded-3 shadow-none" value="{{ request('start_date') }}">
                        <span class="input-group-text bg-transparent border-0 text-muted">-</span>
                        <input type="date" name="end_date" class="form-control border-light rounded-3 shadow-none" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ url()->current() }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
                <!-- Hidden Sort -->
                <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $cheques->total() }} Results</div>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <form id="bulkUpdateForm" action="{{ route('cheques.bulk-update-combined') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="cheque">
                
                <div class="p-2 border-bottom bg-light d-flex gap-2 align-items-center" id="bulkActions" style="display:none !important;">
                    <div class="d-flex align-items-center gap-2">
                        <select name="status" class="form-select form-select-sm" style="width: 160px;" id="bulkStatusSelect" required>
                            <option value="">Select Action...</option>
                            <option value="paid">Mark as Paid</option>
                            <option value="pending">Mark as Pending</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="submit" id="bulkUpdateBtn" class="btn btn-primary btn-sm px-3 shadow-sm" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-check me-1"></i> Update
                        </button>
                    </div>
                    <span class="ms-auto small text-muted fw-bold"><span id="selectedCount" class="text-primary">0</span> records selected</span>
                </div>
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input shadow-none" id="selectAll">
                        </th>
                        @if(($page_title ?? '') == 'Payment Cheques')
                            <th class="py-3 text-muted small text-uppercase">Payment Date</th>
                            <th class="py-3 text-muted small text-uppercase">Payment Method</th>
                            <th class="py-3 text-muted small text-uppercase">Payer/Client</th>
                            <th class="py-3 text-muted small text-uppercase">3rd Party Name</th>
                            <th class="py-3 text-muted small text-uppercase">Amount</th>
                            <th class="py-3 text-muted small text-uppercase">Latest Note</th>
                        @else
                            <th class="py-3 text-muted small text-uppercase">Cheq Date</th>
                            <th class="py-3 text-muted small text-uppercase">Status</th>
                            <th class="py-3 text-muted small text-uppercase">Client Name</th>
                            <th class="py-3 text-muted small text-uppercase">Bank</th>
                            <th class="py-3 text-muted small text-uppercase">Amount (LKR)</th>
                            <th class="py-3 text-muted small text-uppercase">Balance</th>
                            <th class="py-3 text-muted small text-uppercase">3rd Part Status</th>
                            <th class="py-3 text-muted small text-uppercase">3rd Part Name</th>
                            <th class="py-3 text-muted small text-uppercase">CHQ RTN Note</th>
                        @endif
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    <tr>
                        <td class="ps-4">
                            <input type="checkbox" name="cheque_ids[]" value="{{ $cheque->id }}" class="form-check-input shadow-none cheque-checkbox">
                        </td>
                        @if(($page_title ?? '') == 'Payment Cheques')
                            <td class="small text-muted text-nowrap">
                                {{ \Carbon\Carbon::parse($cheque->payment_date)->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold" style="font-size: 0.65rem; border-radius: 6px;">
                                    {{ ucwords(str_replace('_', ' ', $cheque->payment_method)) }}
                                </span>
                            </td>
                            <td>
                                @if($cheque->cheque)
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('cheques.show', $cheque->cheque) }}" class="fw-bold text-dark small text-decoration-none hover-underline text-nowrap">{{ $cheque->cheque->payer_name ?? 'N/A' }}</a>
                                        <span class="text-muted text-nowrap" style="font-size: 0.65rem;">#{{ $cheque->cheque->cheque_number ?? '000000' }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small">Cheque Removed</span>
                                @endif
                            </td>
                            <td class="small fw-medium">{{ $cheque->cheque->payee_name ?? '-' }}</td>
                            <td class="small fw-bold text-nowrap">LKR {{ number_format($cheque->amount, 2) }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 150px;">{{ $cheque->notes ?? '-' }}</td>
                        @else
                            <td class="small text-muted text-nowrap">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
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
                            <td>
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('cheques.show', $cheque) }}" class="fw-bold text-dark small text-decoration-none hover-underline text-nowrap">{{ $cheque->payer_name }}</a>
                                        <span class="text-muted text-nowrap" style="font-size: 0.65rem;">#{{ $cheque->cheque_number }}</span>
                                    </div>
                            </td>
                            <td class="small">{{ $cheque->bank->name }}</td>
                            <td class="small fw-bold text-nowrap">LKR {{ number_format($cheque->amount, 2) }}</td>
                            <td class="small fw-bold text-danger text-nowrap">LKR {{ number_format($cheque->amount - ($cheque->payments_sum_amount ?? 0), 2) }}</td>
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
                            <td class="small">{{ $cheque->payee_name ?? '-' }}</td>
                            <td>
                                @if($cheque->return_reason)
                                    <span class="badge bg-light text-muted fw-normal" style="font-size: 0.65rem;">{{ $cheque->return_reason }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        @endif
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                @php 
                                    $targetCheque = ($page_title ?? '') == 'Payment Cheques' ? $cheque->cheque : $cheque;
                                @endphp
                                @if($targetCheque)
                                    @can('cheque-operation')
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-primary shadow-none btn-notification-animate" 
                                            onclick="openReminderModal({{ $targetCheque->id }}, '{{ $targetCheque->payer_name }}')">
                                        <i class="fa-solid fa-bell"></i>
                                    </button>
                                    @endcan
                                    <a href="{{ route('cheques.show', $targetCheque) }}" class="btn btn-sm btn-icon border-0 text-black shadow-none">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('cheque-edit')
                                    <a href="{{ route('cheques.edit', $targetCheque) }}" class="btn btn-sm btn-icon border-0 text-black shadow-none">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('cheque-delete')
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-black shadow-none" 
                                            onclick="confirmDeleteUrl('{{ route('cheques.destroy', $targetCheque) }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    @endcan
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5 text-muted small">No cheques found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </form>
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

<!-- Reminder Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="reminderModalLabel">Set Reminder</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reminderForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Set a reminder for <span id="reminderClientName" class="fw-bold text-dark"></span></p>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Reminder Date & Time</label>
                        <input type="datetime-local" name="reminder_date" class="form-control border-light bg-light rounded-3 shadow-none" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                        <textarea name="notes" class="form-control border-light bg-light rounded-3 shadow-none" rows="3" placeholder="What should we remind you about?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" style="background: #6366f1; border: none;">Save Reminder</button>
                </div>
            </form>
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

    @keyframes bell-ring {
        0%, 100% { transform: rotate(0); }
        10%, 30%, 50%, 70%, 90% { transform: rotate(10deg); }
        20%, 40%, 60%, 80% { transform: rotate(-10deg); }
    }
    .btn-notification-animate:hover i {
        animation: bell-ring 1s ease-in-out infinite;
    }
    .animate__animated.animate__fadeIn {
        animation-duration: 0.5s;
    }
</style>

<script>
function openReminderModal(id, name) {
    const modal = new bootstrap.Modal(document.getElementById('reminderModal'));
    const form = document.getElementById('reminderForm');
    document.getElementById('reminderClientName').textContent = name;
    form.action = `/cheques/${id}/reminder`;
    modal.show();
}

function confirmDeleteUrl(url) {
    Swal.fire({
        title: 'Delete Cheque?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    })
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    if(selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.cheque-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }

    document.querySelectorAll('.cheque-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.cheque-checkbox:checked').length;
        const bulkActions = document.getElementById('bulkActions');
        if(document.getElementById('selectedCount')) {
            document.getElementById('selectedCount').innerText = checkedCount;
        }
        if(bulkActions) {
            if(checkedCount > 0) {
                bulkActions.style.display = 'flex';
                bulkActions.style.setProperty('display', 'flex', 'important');
            } else {
                bulkActions.style.display = 'none';
                bulkActions.style.setProperty('display', 'none', 'important');
            }
        }
    }
    
    const bulkForm = document.getElementById('bulkUpdateForm');
    if(bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const count = document.querySelectorAll('.cheque-checkbox:checked').length;
            if (count === 0) {
                 Swal.fire('Error', 'No cheques selected', 'error');
                 return;
            }
            Swal.fire({
                title: 'Update ' + count + ' Cheques?',
                text: "Are you sure you want to proceed?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                confirmButtonText: 'Yes, update them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    }
});
</script>
@endsection
