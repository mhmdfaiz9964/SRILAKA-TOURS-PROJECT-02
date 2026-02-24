@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Top Summary -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">Daily Ledger</h4>
                <span class="text-muted small">Comprehensive view of business cash flow</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold"
                    style="background: #6366f1; border: none;" data-bs-toggle="modal" data-bs-target="#createLedgerModal">
                    <i class="fa-solid fa-plus me-2"></i> Create Entry
                </button>
                <div class="dropdown">
                    <button class="btn btn-white border-light rounded-pill px-3 shadow-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="fa-solid fa-file-export me-2"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4">
                        <a class="dropdown-item p-2 px-3"
                            href="{{ route('reports.daily-ledger', array_merge(request()->all(), ['export' => 'excel'])) }}">
                            <i class="fa-solid fa-file-excel text-success me-2"></i> Excel Format
                        </a>
                        <a class="dropdown-item p-2 px-3"
                            href="{{ route('reports.daily-ledger', array_merge(request()->all(), ['export' => 'pdf'])) }}">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i> PDF Format
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2-4 col-sm-4 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                    <div class="card-body p-3 text-center">
                        <div class="text-success small fw-bold text-uppercase mb-1">Total History Income</div>
                        <div class="fw-bold text-success fs-5">LKR {{ number_format($historySummary['total_income'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-4 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                    <div class="card-body p-3 text-center">
                        <div class="text-danger small fw-bold text-uppercase mb-1">Total History Expense</div>
                        <div class="fw-bold text-danger fs-5">LKR {{ number_format($historySummary['total_expense'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-4 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #eff6ff;">
                    <div class="card-body p-3 text-center">
                        <div class="text-primary small fw-bold text-uppercase mb-1">Balance</div>
                        <div class="fw-bold text-primary fs-5">LKR {{ number_format($historySummary['balance'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-4 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #faf5ff;">
                    <div class="card-body p-3 text-center">
                        <div class="text-purple small fw-bold text-uppercase mb-1" style="color: #9333ea;">Total A/C Balance
                        </div>
                        <div class="fw-bold fs-5" style="color: #9333ea;">LKR
                            {{ number_format($historySummary['total_ac_balance'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-4 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fffbeb;">
                    <div class="card-body p-3 text-center">
                        <div class="text-warning small fw-bold text-uppercase mb-1">Total Bank Deposit</div>
                        <div class="fw-bold text-warning fs-5">LKR
                            {{ number_format($historySummary['total_bank_deposit'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & History Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0">Daily Ledger Entries</h6>
                <form action="{{ route('reports.daily-ledger') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <select name="per_page" class="form-select form-select-sm border-light rounded-pill px-3 shadow-none"
                        onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 Rows</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Rows</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Rows</option>
                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500 Rows</option>
                        <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000 Rows</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All Rows</option>
                    </select>
                    <select name="filter" class="form-select form-select-sm border-light rounded-pill px-3 shadow-none"
                        onchange="this.form.submit()">
                        <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="last_7_days" {{ $filter == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last_week" {{ $filter == 'last_week' ? 'selected' : '' }}>Last Week</option>
                        <option value="last_month" {{ $filter == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="last_year" {{ $filter == 'last_year' ? 'selected' : '' }}>Last Year</option>
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4 py-3 small text-muted text-uppercase">Date</th>
                                <th class="py-3 small text-muted text-end text-uppercase">Income</th>
                                <th class="py-3 small text-muted text-end text-uppercase">Expense</th>
                                <th class="py-3 small text-muted text-end text-uppercase">Bank Deposit</th>
                                <th class="py-3 small text-muted text-end text-uppercase">A/c Sales</th>
                                <th class="py-3 small text-muted text-end text-uppercase pe-4">Balance</th>
                                <th class="py-3 small text-muted text-center text-uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">
                                        {{ \Carbon\Carbon::parse($entry->date)->format('d M, Y') }}
                                    </td>
                                    <td class="text-end fw-bold text-success">{{ number_format($entry->total_income, 2) }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($entry->total_expense, 2) }}</td>
                                    <td class="text-end fw-bold text-warning">{{ number_format($entry->bank_deposit, 2) }}</td>
                                    <td class="text-end text-muted">{{ number_format($entry->ac_sales, 2) }}</td>
                                    <td class="text-end fw-bold {{ $entry->total >= 0 ? 'text-success' : 'text-danger' }} pe-4">
                                        {{ number_format($entry->total, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button"
                                                class="btn btn-sm btn-light border-0 text-primary bg-primary bg-opacity-10 rounded-pill px-3"
                                                onclick="editLedger('{{ $entry->date }}')">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3"
                                                onclick="deleteLedger({{ $entry->id }}, '{{ $entry->date }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted small">No ledger entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($ledgerEntries->hasPages())
                    <div class="p-3 border-top">
                        {{ $ledgerEntries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Ledger Modal -->
    <div class="modal fade" id="createLedgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h5 class="fw-bold mb-0">Create Ledger Entry</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <p class="text-muted mb-4">Please select a date to start recording income and expenses.</p>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light border-0"><i
                                class="fa-solid fa-calendar text-primary"></i></span>
                        <input type="date" id="createLedgerDate" class="form-control border-light shadow-none"
                            value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4"
                        style="background: #6366f1; border: none;" onclick="startNewLedger()">
                        Continue <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Ledger Modal (Huge Modal for Entries) -->
    <div class="modal fade" id="editLedgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="height: 90vh;">
                <div class="modal-header border-bottom p-3">
                    <div>
                        <h5 class="fw-bold mb-0" id="editModalTitle">Edit Ledger Data</h5>
                        <p class="text-muted small mb-0" id="editModalSubtitle"></p>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 overflow-auto">
                    <form id="ledgerForm" action="{{ route('reports.daily-ledger.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" id="ledgerDateInput">
                        <div class="container-fluid py-3">
                            <div class="row g-4">
                                <!-- Income Section -->
                                <div class="col-md-6 border-end">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-bold text-success mb-0"><i
                                                class="fa-solid fa-arrow-down me-2"></i>Income Entries</h6>
                                        <button type="button" class="btn btn-sm btn-light text-success rounded-pill px-3"
                                            onclick="addEntry('income')">
                                            <i class="fa-solid fa-plus me-1"></i> Add
                                        </button>
                                    </div>
                                    <table class="table table-sm align-middle">
                                        <tbody id="incomeEntriesBody"></tbody>
                                    </table>
                                </div>
                                <!-- Expense Section -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-bold text-danger mb-0"><i
                                                class="fa-solid fa-arrow-up me-2"></i>Expense & Bank Deposit</h6>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-pill px-3"
                                                onclick="addEntry('expense')">
                                                <i class="fa-solid fa-plus me-1"></i> Expense
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-light text-warning rounded-pill px-3"
                                                onclick="addSalary()">
                                                <i class="fa-solid fa-plus me-1"></i> Salary
                                            </button>
                                        </div>
                                    </div>
                                    <table class="table table-sm align-middle">
                                        <tbody id="expenseEntriesBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light bg-opacity-10 p-3">
                    <div class="me-auto d-flex align-items-center gap-4 border-end pe-4">
                        <div class="text-center">
                            <div class="small text-muted text-uppercase mb-0">Total Income</div>
                            <div class="fw-bold text-success" id="modalTotalIncomeDisplay">0.00</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted text-uppercase mb-0">Total Outflow</div>
                            <div class="fw-bold text-danger" id="modalTotalOutflowDisplay">0.00</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        onclick="downloadLedgerImage()">
                        <i class="fa-solid fa-camera me-2"></i> Screenshot
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm"
                        style="background: #6366f1; border: none;" onclick="submitLedgerForm()">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="deleteLedgerForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <style>
        .col-md-2-4 {
            width: 20%;
        }

        @media (max-width: 992px) {
            .col-md-2-4 {
                width: 33.33%;
            }
        }

        @media (max-width: 768px) {
            .col-md-2-4 {
                width: 50%;
            }
        }

        @media (max-width: 576px) {
            .col-md-2-4 {
                width: 100%;
            }
        }

        .modal-xl {
            max-width: 1140px;
        }

        #editLedgerModal .modal-content {
            background: #fff;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        let editModal = new bootstrap.Modal(document.getElementById('editLedgerModal'));
        let createModal = new bootstrap.Modal(document.getElementById('createLedgerModal'));
        let rowCounter = 0;

        function startNewLedger() {
            let date = document.getElementById('createLedgerDate').value;
            createModal.hide();
            editLedger(date);
        }

        function editLedger(date) {
            rowCounter = 0;
            document.getElementById('editModalTitle').innerText = 'Ledger Data: ' + date;
            document.getElementById('editModalSubtitle').innerText = 'Manage income and outflow entries';
            document.getElementById('ledgerDateInput').value = date;

            document.getElementById('incomeEntriesBody').innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';
            document.getElementById('expenseEntriesBody').innerHTML = '';

            fetch(`/reports/daily-ledger/details/${date}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('incomeEntriesBody').innerHTML = '';
                    document.getElementById('expenseEntriesBody').innerHTML = '';

                    // Default heads logic (if empty)
                    if (data.income.length === 0) {
                        ['A/c Sales', 'Cash Sales', 'Old payment'].forEach(d => addEntryRow('income', d, 0));
                    } else {
                        data.income.forEach(item => addEntryRow('income', item.description, item.amount, item.id));
                    }

                    if (data.expense.length === 0) {
                        ['Transport', 'Food', 'Bank Deposit', 'Other'].forEach(d => addEntryRow('expense', d, 0));
                    } else {
                        data.expense.forEach(item => addEntryRow('expense', item.description, item.amount, item.id));
                    }

                    data.salaries.forEach(item => addSalaryRow(item.employee_name, item.amount, item.id));

                    calculateModalTotals();
                    editModal.show();
                });
        }

        function addEntry(type) {
            addEntryRow(type, '', 0);
        }

        function addSalary() {
            addSalaryRow('', 0);
        }

        function addEntryRow(type, description, amount, id = '') {
            let body = document.getElementById(type + 'EntriesBody');
            let key = 'new_' + rowCounter++;
            let isDefault = ['A/c Sales', 'Cash Sales', 'Old payment', 'Transport', 'Food', 'Bank Deposit', 'Other'].includes(description);
            let formattedAmount = formatValue(amount);

            let html = `
                            <tr id="row_${key}">
                                <td>
                                    <input type="hidden" name="entries[${key}][id]" value="${id}">
                                    <input type="hidden" name="entries[${key}][type]" value="${type}">
                                    <input type="text" name="entries[${key}][description]" value="${description}" 
                                        class="form-control form-control-sm border-0 bg-transparent shadow-none fw-medium" 
                                        placeholder="Description" ${isDefault ? 'readonly' : ''} required>
                                </td>
                                <td style="width: 150px;">
                                    <input type="text" name="entries[${key}][amount]" value="${formattedAmount}" 
                                        class="form-control form-control-sm text-end border-light bg-light rounded-2 shadow-none modal-amount ${type == 'income' && description != 'A/c Sales' ? 'modal-income' : (type == 'expense' ? 'modal-expense' : '')}" 
                                        oninput="handleInput(this)">
                                </td>
                                <td class="text-center" style="width: 50px;">
                                    ${isDefault ? '' : `<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('row_${key}').remove(); calculateModalTotals();"><i class="fa-solid fa-times"></i></button>`}
                                </td>
                            </tr>
                        `;
            body.insertAdjacentHTML('beforeend', html);
        }

        function addSalaryRow(name, amount, id = '') {
            let body = document.getElementById('expenseEntriesBody');
            let key = 'sal_' + rowCounter++;
            let formattedAmount = formatValue(amount);
            let html = `
                            <tr id="row_${key}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-user-tie text-warning small"></i>
                                        <input type="hidden" name="salaries[${key}][id]" value="${id}">
                                        <input type="text" name="salaries[${key}][employee_name]" value="${name}" 
                                            class="form-control form-control-sm border-0 bg-transparent shadow-none fw-medium" 
                                            placeholder="Employee Name" required>
                                    </div>
                                </td>
                                <td style="width: 150px;">
                                    <input type="text" name="salaries[${key}][amount]" value="${formattedAmount}" 
                                        class="form-control form-control-sm text-end border-light bg-light rounded-2 shadow-none modal-amount modal-salary" 
                                        oninput="handleInput(this)">
                                </td>
                                <td class="text-center" style="width: 50px;">
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('row_${key}').remove(); calculateModalTotals();"><i class="fa-solid fa-times"></i></button>
                                </td>
                            </tr>
                        `;
            body.insertAdjacentHTML('beforeend', html);
        }

        function formatValue(val) {
            if (val === undefined || val === null || isNaN(val)) return '0.00';
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(val);
        }

        function handleInput(el) {
            // Store cursor position
            let start = el.selectionStart;
            let originalLength = el.value.length;

            // Strip non-digits and non-dots
            let clean = el.value.replace(/[^\d.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) clean = parts[0] + '.' + parts.slice(1).join('');

            // Format with commas but keep decimals as they are being typed
            if (parts[0]) {
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
            el.value = parts.join('.');

            // Adjust cursor position
            let newLength = el.value.length;
            el.setSelectionRange(start + (newLength - originalLength), start + (newLength - originalLength));

            calculateModalTotals();
        }

        function calculateModalTotals() {
            let income = 0;
            let outflow = 0;
            document.querySelectorAll('.modal-income').forEach(el => {
                let val = parseFloat(el.value.replace(/,/g, '')) || 0;
                income += val;
            });
            document.querySelectorAll('.modal-expense').forEach(el => {
                let val = parseFloat(el.value.replace(/,/g, '')) || 0;
                outflow += val;
            });
            document.querySelectorAll('.modal-salary').forEach(el => {
                let val = parseFloat(el.value.replace(/,/g, '')) || 0;
                outflow += val;
            });

            const formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            document.getElementById('modalTotalIncomeDisplay').innerText = formatter.format(income);
            document.getElementById('modalTotalOutflowDisplay').innerText = formatter.format(outflow);
        }

        function submitLedgerForm() {
            let form = document.getElementById('ledgerForm');

            // Strip commas before submission
            document.querySelectorAll('.modal-amount').forEach(el => {
                el.value = el.value.replace(/,/g, '');
            });

            let btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            form.submit();
        }

        function deleteLedger(id, date) {
            Swal.fire({
                title: 'Delete Ledger?',
                text: "Deleting the ledger for " + date + " will remove all associated income, expense, and salary entries. This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete Everything'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('deleteLedgerForm');
                    form.action = `/reports/daily-ledger/delete/${id}`;
                    form.submit();
                }
            });
        }

        function downloadLedgerImage() {
            const modalBody = document.querySelector('#editLedgerModal .modal-content');
            const date = document.getElementById('ledgerDateInput').value;

            // Hide buttons temporarily
            const footerButtons = document.querySelector('#editLedgerModal .modal-footer').children;
            for (let btn of footerButtons) btn.style.display = 'none';

            html2canvas(modalBody, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                // Restore buttons
                for (let btn of footerButtons) btn.style.display = '';

                const link = document.createElement('a');
                link.download = `daily-ledger-${date}.jpg`;
                link.href = canvas.toDataURL('image/jpeg', 0.9);
                link.click();
            });
        }
    </script>
@endsection