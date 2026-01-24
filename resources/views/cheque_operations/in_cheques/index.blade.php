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
                ['label' => 'All In Cheques', 'key' => 'all', 'icon' => 'fa-list', 'color' => '#64748b', 'bg' => '#f8fafc', 'status' => ''],
                ['label' => 'In Hand', 'key' => 'in_hand', 'icon' => 'fa-hand-holding-dollar', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'status' => 'received'],
                ['label' => 'Deposited', 'key' => 'deposited', 'icon' => 'fa-building-columns', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'status' => 'deposited'],
                ['label' => 'Transferred', 'key' => 'transferred', 'icon' => 'fa-right-left', 'color' => '#8b5cf6', 'bg' => '#f5f3ff', 'status' => 'transferred_to_third_party'],
                ['label' => 'Returned', 'key' => 'returned', 'icon' => 'fa-rotate-left', 'color' => '#ef4444', 'bg' => '#fef2f2', 'status' => 'returned'],
                ['label' => 'Realized', 'key' => 'realized', 'icon' => 'fa-circle-check', 'color' => '#10b981', 'bg' => '#ecfdf5', 'status' => 'realized'],
                ['label' => 'Deposit Today', 'key' => 'to_deposit_today', 'icon' => 'fa-calendar-day', 'color' => '#06b6d4', 'bg' => '#ecfeff', 'status' => 'today'],
                ['label' => 'Overdue', 'key' => 'overdue', 'icon' => 'fa-clock', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'status' => 'overdue'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('in-cheques.index', ['status' => $card['status']]) }}" class="text-decoration-none">
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
            <form action="{{ route('in-cheques.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Search</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="Name or #...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Payer Name</label>
                    <select name="payer_name" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Payers</option>
                        @foreach($payers as $payer)
                            <option value="{{ $payer }}" {{ request('payer_name') == $payer ? 'selected' : '' }}>{{ $payer }}</option>
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
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>In Hand</option>
                        <option value="deposited" {{ request('status') == 'deposited' ? 'selected' : '' }}>Deposited</option>
                        <option value="transferred_to_third_party" {{ request('status') == 'transferred_to_third_party' ? 'selected' : '' }}>Transferred</option>
                        <option value="realized" {{ request('status') == 'realized' ? 'selected' : '' }}>Realized</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="today" {{ request('status') == 'today' ? 'selected' : '' }}>Deposit Today</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Cheque Date Range</label>
                    <input type="text" id="daterange" class="form-control form-control-sm border-light rounded-3" placeholder="Select date range..." readonly>
                    <input type="hidden" name="from_date" id="from_date" value="{{ request('from_date') }}">
                    <input type="hidden" name="to_date" id="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('in-cheques.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $cheques->total() }} Results</div>
                </div>
            </form>
        </div>

        <script>
        $(function() {
            var start = moment('{{ request("from_date") }}' || null);
            var end = moment('{{ request("to_date") }}' || null);

            function cb(start, end) {
                if (start.isValid() && end.isValid()) {
                    $('#daterange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                    $('#from_date').val(start.format('YYYY-MM-DD'));
                    $('#to_date').val(end.format('YYYY-MM-DD'));
                } else {
                    $('#daterange').val('');
                    $('#from_date').val('');
                    $('#to_date').val('');
                }
            }

            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'DD/MM/YYYY'
                }
            });

            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                cb(picker.startDate, picker.endDate);
            });

            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#from_date').val('');
                $('#to_date').val('');
            });

            @if(request('from_date') && request('to_date'))
                cb(start, end);
            @endif
        });
        </script>

        <div class="table-responsive">
            <form id="bulkUpdateForm" action="{{ route('cheques.bulk-update') }}" method="POST">
                @csrf
                <div class="p-2 border-bottom bg-light d-flex gap-2" id="bulkActions" style="display:none !important;">
                    <select name="status" class="form-select form-select-sm" style="width: 150px;" id="bulkStatusSelect" required>
                        <option value="">Select Action</option>
                        <option value="deposited">Mark Deposited</option>
                        <option value="realized">Mark Realized</option>
                        <option value="returned">Mark Returned</option>
                        <option value="third_party">Transfer to 3rd Party</option>
                    </select>
                    <input type="text" name="third_party_name" id="bulkThirdPartyName" class="form-control form-control-sm d-none" placeholder="3rd Party Name" style="width: 200px;">
                    <button type="submit" class="btn btn-primary btn-sm">Update Selected</button>
                    <span class="ms-auto small text-muted align-self-center"><span id="selectedCount">0</span> selected</span>
                </div>

                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light bg-opacity-10 border-bottom">
                            <th class="ps-4 py-3" style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th class="py-3 text-muted small text-uppercase">Type</th>
                            <th class="py-3 text-muted small text-uppercase">Cheq Date</th>
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
                            <td class="ps-4"><input type="checkbox" name="cheque_ids[]" value="{{ $cheque->id }}" class="cheque-checkbox"></td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1" style="background: #eff6ff; color: #3b82f6; font-size: 0.65rem;">
                                    IN
                                </span>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
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
                                    <button type="button" class="btn btn-sm btn-icon border-0 text-danger shadow-none" onclick="confirmDeleteCheque('{{ route('in-cheques.destroy', $cheque) }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted small">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        <div class="p-4 border-top">
            {{ $cheques->links() }}
        </div>
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.cheque-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });

    document.querySelectorAll('.cheque-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.cheque-checkbox:checked').length;
        const bulkActions = document.getElementById('bulkActions');
        document.getElementById('selectedCount').innerText = checkedCount;
        if(checkedCount > 0) {
            bulkActions.style.display = 'flex';
            bulkActions.style.setProperty('display', 'flex', 'important');
        } else {
            bulkActions.style.display = 'none';
            bulkActions.style.setProperty('display', 'none', 'important');
        }
    }

    document.getElementById('bulkStatusSelect').addEventListener('change', function() {
        const thirdPartyInput = document.getElementById('bulkThirdPartyName');
        if(this.value === 'third_party') {
            thirdPartyInput.classList.remove('d-none');
            thirdPartyInput.required = true;
        } else {
            thirdPartyInput.classList.add('d-none');
            thirdPartyInput.required = false;
        }
    });

    function confirmDeleteCheque(url) {
        if(confirm('Delete this record?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<style>
    .card-stat { transition: all 0.2s ease-in-out; border: 1px solid transparent !important; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: rgba(99, 102, 241, 0.2) !important; }
    .btn-icon:hover { background: #f1f5f9; border-radius: 8px; }
</style>
@endsection
