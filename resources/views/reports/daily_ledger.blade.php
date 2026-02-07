@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header & Date Filter -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Daily Ledger</h4>
            <span class="text-muted small">Day Book / Cash Book (Manual Entry)</span>
        </div>
        <form action="{{ route('reports.daily-ledger') }}" method="GET" class="d-flex gap-2" id="dateFilterForm">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control form-control-sm border-light rounded-3 shadow-none" id="dateInput">
            <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">View</button>
            <button type="button" onclick="resetForm()" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                <i class="fa-solid fa-rotate-right me-1"></i> Reset
            </button>
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle shadow-sm" data-bs-toggle="dropdown" type="button">
                    <i class="fa-solid fa-file-export text-black"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 180px;">
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3" href="{{ route('reports.daily-ledger', ['date' => $date->format('Y-m-d'), 'export' => 'excel']) }}">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-excel text-success"></i>
                        </div>
                        <span class="small fw-bold">Excel Format</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1" href="{{ route('reports.daily-ledger', ['date' => $date->format('Y-m-d'), 'export' => 'pdf']) }}">
                        <div class="bg-danger bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-pdf text-danger"></i>
                        </div>
                        <span class="small fw-bold">PDF Format</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Income -->
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                <div class="card-body p-3 text-center">
                    <div class="text-success small fw-bold text-uppercase mb-1">Total Daily Income</div>
                    <div class="fw-bold text-success fs-5">+ LKR <span id="topTotalIncome">{{ number_format($totalIncome, 2) }}</span></div>
                </div>
            </div>
        </div>
        <!-- Expenses -->
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                <div class="card-body p-3 text-center">
                    <div class="text-danger small fw-bold text-uppercase mb-1">Total Money Out</div>
                    <div class="fw-bold text-danger fs-5">- LKR <span id="topTotalExpenses">{{ number_format($totalExpenses + $totalSalary, 2) }}</span></div>
                </div>
            </div>
        </div>
        <!-- Closing Balance -->
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #eff6ff;">
                <div class="card-body p-3 text-center">
                    <div class="text-primary small fw-bold text-uppercase mb-1">Closing Balance</div>
                    <div class="fw-bold text-primary fs-5">LKR <span id="topClosingBalance">{{ number_format($closingBalance, 2) }}</span></div>
                </div>
            </div>
        </div>
        <input type="hidden" id="openingBalanceVal" value="{{ $openingBalance }}">
    </div>

    <form action="{{ route('reports.daily-ledger.update') }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        
        <div class="row g-4 mb-4">
            <!-- Money In Section -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-arrow-down text-success small"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Income Entries</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-light text-success border-0 rounded-pill px-2" onclick="addEntryRow('income')">
                            <i class="fa-solid fa-plus-circle"></i> Add
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-2 small text-muted">Description</th>
                                        <th class="py-2 small text-muted text-end" style="width: 180px;">Amount (LKR)</th>
                                        <th class="py-2 small text-muted text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="incomeRows">
                                    @foreach($incomeEntries as $entry)
                                    <tr id="ledger_row_{{ $entry->id }}">
                                        <td class="ps-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="text" name="entries[{{ $entry->id }}][description]" value="{{ $entry->description }}" class="form-control form-control-sm border-0 bg-transparent shadow-none fw-medium" readonly>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" 
                                                   class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring entry-amount income-amount" 
                                                   data-description="{{ $entry->description }}"
                                                   oninput="calculateLiveBalance()">
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-link text-success p-0" onclick="duplicateRow('ledger', {{ $entry->id }}, 'income')" title="Duplicate">
                                                    <i class="fa-solid fa-plus-circle"></i>
                                                </button>
                                                @if(!in_array($entry->description, ['A/c Sales', 'Cash Sales', 'Old payment']))
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow('ledger', {{ $entry->id }})">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-success bg-opacity-10">
                                    <tr>
                                        <td class="ps-4 fw-bold text-success small">Total Income</td>
                                        <td class="text-end fw-bold text-success">LKR <span id="totalIncomeDisplay">{{ number_format($totalIncome, 2) }}</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Money Out Section (Combined Expenses & Salary) -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                             <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-arrow-up text-danger small"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Expense & Salary Entries</h6>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-pill px-2" onclick="addEntryRow('expense')">
                                <i class="fa-solid fa-plus-circle"></i> Expense
                            </button>
                            <button type="button" class="btn btn-sm btn-light text-warning border-0 rounded-pill px-2" onclick="addSalaryRow()">
                                <i class="fa-solid fa-plus-circle"></i> Salary
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="expenseTable">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-2 small text-muted">Description / Employee</th>
                                        <th class="py-2 small text-muted text-end" style="width: 180px;">Amount (LKR)</th>
                                        <th class="py-2 small text-muted text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="expenseRows">
                                    <!-- Regular Expenses -->
                                    @foreach($expenseEntries as $entry)
                                    <tr id="ledger_row_{{ $entry->id }}">
                                        <td class="ps-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="text" name="entries[{{ $entry->id }}][description]" value="{{ $entry->description }}" class="form-control form-control-sm border-0 bg-transparent shadow-none fw-medium" readonly>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" 
                                                   class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring entry-amount expense-amount" 
                                                   data-description="{{ $entry->description }}"
                                                   oninput="calculateLiveBalance()">
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="duplicateRow('ledger', {{ $entry->id }}, 'expense')" title="Duplicate">
                                                    <i class="fa-solid fa-plus-circle"></i>
                                                </button>
                                                @if(!in_array($entry->description, ['Transport', 'Food', 'Bank Deposit', 'Other']))
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow('ledger', {{ $entry->id }})">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                    <!-- Salary Entries -->
                                    @foreach($salaryEntries as $index => $salary)
                                    <tr id="salary_row_{{ $salary->id }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-user-tie text-warning small"></i>
                                                <input type="hidden" name="salaries[{{ $index }}][id]" value="{{ $salary->id }}">
                                                <input type="text" name="salaries[{{ $index }}][employee_name]" value="{{ $salary->employee_name }}" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" placeholder="Employee Name" required>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" step="0.01" name="salaries[{{ $index }}][amount]" value="{{ $salary->amount }}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring salary-amount px-2" required oninput="calculateLiveBalance()">
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-link text-warning p-0" onclick="duplicateRow('salary', {{ $salary->id }})" title="Duplicate">
                                                    <i class="fa-solid fa-plus-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow('salary', {{ $salary->id }})">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-danger bg-opacity-10">
                                    <tr>
                                        <td class="ps-4 fw-bold text-danger small">Total Money Out</td>
                                        <td class="text-end fw-bold text-danger">LKR <span id="totalExpenseDisplayCombined">0.00</span></td>
                                        <td></td>
                                    </tr>
                                    <tr class="small">
                                        <td class="ps-4 text-muted">Expense: <span id="totalExpenseSub">0.00</span> | Salary: <span id="totalSalarySub">0.00</span></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold" style="background: #6366f1; border: none;">
                <i class="fa-solid fa-save me-2"></i> Save Ledger Data
            </button>
        </div>
    </form>

    <!-- History Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                <div class="card-body p-3 text-center">
                    <div class="text-success small fw-bold text-uppercase mb-1">Total History Income</div>
                    <div class="fw-bold text-success fs-5">LKR {{ number_format($historySummary['total_income'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                <div class="card-body p-3 text-center">
                    <div class="text-danger small fw-bold text-uppercase mb-1">Total History Expense</div>
                    <div class="fw-bold text-danger fs-5">LKR {{ number_format($historySummary['total_expense'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fffbeb;">
                <div class="card-body p-3 text-center">
                    <div class="text-warning small fw-bold text-uppercase mb-1">Total Salary Paid</div>
                    <div class="fw-bold text-warning fs-5">LKR <span id="historySalaryPaid">{{ number_format($historySummary['total_salary'], 2) }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #eff6ff;">
                <div class="card-body p-3 text-center">
                    <div class="text-primary small fw-bold text-uppercase mb-1">Total A/C Balance</div>
                    <div class="fw-bold text-primary fs-5">LKR {{ number_format($historySummary['total_ac_balance'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Created Ledger History Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="fw-bold mb-0">Created Ledger History Summary</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-10">
                        <tr>
                            <th class="ps-4 py-3 small text-muted text-uppercase">Date</th>
                            <th class="py-3 small text-muted text-end text-uppercase">In (Income)</th>
                            <th class="py-3 small text-muted text-end text-uppercase">Out (Expense)</th>
                            <th class="py-3 small text-muted text-end text-uppercase">Salary</th>
                            <th class="py-3 small text-muted text-end text-uppercase pe-4">Balance</th>
                            <th class="py-3 small text-muted text-end text-uppercase pe-4" style="min-width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ledgerEntries as $entry)
                        <tr>
                            <td class="ps-4 fw-medium text-dark">{{ \Carbon\Carbon::parse($entry->date)->format('d M, Y') }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($entry->total_income, 2) }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($entry->total_expense, 2) }}</td>
                            <td class="text-end fw-bold text-warning">{{ number_format($entry->total_salary, 2) }}</td>
                             <td class="text-end fw-bold {{ $entry->total >= 0 ? 'text-success' : 'text-danger' }} pe-4">
                                {{ number_format($entry->total, 2) }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-light border-0 text-primary bg-primary bg-opacity-10 rounded-pill px-3" onclick="showLedgerDetails('{{ $entry->date }}', '{{ $entry->id }}')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border-0 text-success bg-success bg-opacity-10 rounded-pill px-3" onclick="window.location='{{ route('reports.daily-ledger', ['date' => $entry->date]) }}'">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3" onclick="deleteEntry({{ $entry->id }}, '{{ $entry->date }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="ledgerDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
             <div class="modal-header border-bottom-0 pb-0">
                <div>
                     <h5 class="modal-title fw-bold" id="modalDateTitle">Ledger Details</h5>
                     <p class="text-muted small mb-0">Summary of income and expenses</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3 border-bottom pb-2">Income</h6>
                        <ul class="list-group list-group-flush" id="modalIncomeList"></ul>
                         <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                            <span>Total Income</span>
                            <span class="text-success" id="modalTotalIncome">0.00</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-danger mb-3 border-bottom pb-2">Outflow (Expense & Salary)</h6>
                        <ul class="list-group list-group-flush" id="modalExpenseList"></ul>
                         <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                            <span>Total Outflow</span>
                            <span class="text-danger" id="modalTotalExpense">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    let rowCounter = Date.now();

    function calculateLiveBalance() {
        let totalIncome = 0;
        let totalExpense = 0;
        let totalSalary = 0;

        document.querySelectorAll('.income-amount').forEach(el => {
            if(el.dataset.description !== 'A/c Sales') {
                totalIncome += parseFloat(el.value || 0);
            }
        });

        document.querySelectorAll('.expense-amount').forEach(el => {
            totalExpense += parseFloat(el.value || 0);
        });

        document.querySelectorAll('.salary-amount').forEach(el => {
            totalSalary += parseFloat(el.value || 0);
        });

        const openingBalance = parseFloat(document.getElementById('openingBalanceVal').value || 0);
        const closingBalance = openingBalance + totalIncome - totalExpense - totalSalary;

        // Update Footers
        if(document.getElementById('totalIncomeDisplay')) document.getElementById('totalIncomeDisplay').innerText = totalIncome.toFixed(2);
        if(document.getElementById('totalExpenseDisplayCombined')) document.getElementById('totalExpenseDisplayCombined').innerText = (totalExpense + totalSalary).toFixed(2);
        if(document.getElementById('totalExpenseSub')) document.getElementById('totalExpenseSub').innerText = totalExpense.toFixed(2);
        if(document.getElementById('totalSalarySub')) document.getElementById('totalSalarySub').innerText = totalSalary.toFixed(2);

        // Update Top Cards
        if(document.getElementById('topTotalIncome')) document.getElementById('topTotalIncome').innerText = totalIncome.toFixed(2);
        if(document.getElementById('topTotalExpenses')) document.getElementById('topTotalExpenses').innerText = (totalExpense + totalSalary).toFixed(2);
        if(document.getElementById('topClosingBalance')) document.getElementById('topClosingBalance').innerText = closingBalance.toFixed(2);
    }

    function addEntryRow(type) {
        const tbody = (type === 'income') ? document.getElementById('incomeRows') : document.getElementById('expenseRows');
        const rowId = 'new_row_' + rowCounter++;
        const icon = (type === 'income') ? 'fa-arrow-down text-success' : 'fa-arrow-up text-danger';
        
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td class="ps-4">
                <input type="text" name="entries[${rowId}][description]" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" placeholder="Entry Description" required>
                <input type="hidden" name="entries[${rowId}][type]" value="${type}">
            </td>
            <td class="text-end">
                <input type="number" step="0.01" name="entries[${rowId}][amount]" value="0" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring entry-amount ${type}-amount" required oninput="calculateLiveBalance()">
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-link text-primary p-0" onclick="duplicateNewRow('${rowId}', 'ledger')" title="Duplicate">
                        <i class="fa-solid fa-plus-circle"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('${rowId}').remove(); calculateLiveBalance();">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
        calculateLiveBalance();
    }

    function addSalaryRow() {
        const tbody = document.getElementById('expenseRows');
        const rowId = 'new_salary_' + rowCounter++;
        
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td class="ps-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-tie text-warning small"></i>
                    <input type="text" name="salaries[${rowId}][employee_name]" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" placeholder="Employee Name" required>
                </div>
            </td>
            <td class="text-end">
                <input type="number" step="0.01" name="salaries[${rowId}][amount]" value="0" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring salary-amount" required oninput="calculateLiveBalance()">
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-link text-warning p-0" onclick="duplicateNewRow('${rowId}', 'salary')" title="Duplicate">
                        <i class="fa-solid fa-plus-circle"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('${rowId}').remove(); calculateLiveBalance();">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
        calculateLiveBalance();
    }

    function duplicateRow(model, id, type) {
        if(model === 'ledger') {
            const desc = document.querySelector(`tr#ledger_row_${id} input[name*="[description]"]`).value;
            const amount = document.querySelector(`tr#ledger_row_${id} input[name*="[amount]"]`).value;
            const tbody = (type === 'income') ? document.getElementById('incomeRows') : document.getElementById('expenseRows');
            const rowId = 'new_row_' + rowCounter++;
            
            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.innerHTML = `
                <td class="ps-4">
                    <input type="text" name="entries[${rowId}][description]" value="${desc}" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" required>
                    <input type="hidden" name="entries[${rowId}][type]" value="${type}">
                </td>
                <td class="text-end">
                    <input type="number" step="0.01" name="entries[${rowId}][amount]" value="${amount}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring entry-amount ${type}-amount" required oninput="calculateLiveBalance()">
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-sm btn-link text-primary p-0" onclick="duplicateNewRow('${rowId}', 'ledger')" title="Duplicate">
                            <i class="fa-solid fa-plus-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('${rowId}').remove(); calculateLiveBalance();">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        } else {
            const empName = document.querySelector(`tr#salary_row_${id} input[name*="[employee_name]"]`).value;
            const amount = document.querySelector(`tr#salary_row_${id} input[name*="[amount]"]`).value;
            const tbody = document.getElementById('expenseRows');
            const rowId = 'new_salary_' + rowCounter++;
            
            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-tie text-warning small"></i>
                        <input type="text" name="salaries[${rowId}][employee_name]" value="${empName}" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" required>
                    </div>
                </td>
                <td class="text-end">
                    <input type="number" step="0.01" name="salaries[${rowId}][amount]" value="${amount}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring salary-amount" required oninput="calculateLiveBalance()">
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-sm btn-link text-warning p-0" onclick="duplicateNewRow('${rowId}', 'salary')" title="Duplicate">
                            <i class="fa-solid fa-plus-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('${rowId}').remove(); calculateLiveBalance();">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }
        calculateLiveBalance();
    }

    function duplicateNewRow(oldRowId, model) {
        const oldRow = document.getElementById(oldRowId);
        const newRowId = (model === 'ledger' ? 'new_row_' : 'new_salary_') + rowCounter++;
        const tr = document.createElement('tr');
        tr.id = newRowId;

        if(model === 'ledger') {
            const desc = oldRow.querySelector('input[name*="[description]"]').value;
            const amount = oldRow.querySelector('input[name*="[amount]"]').value;
            const type = oldRow.querySelector('input[name*="[type]"]').value;
            tr.innerHTML = `
                <td class="ps-4">
                    <input type="text" name="entries[${newRowId}][description]" value="${desc}" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" required>
                    <input type="hidden" name="entries[${newRowId}][type]" value="${type}">
                </td>
                <td class="text-end">
                    <input type="number" step="0.01" name="entries[${newRowId}][amount]" value="${amount}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring entry-amount ${type}-amount" required oninput="calculateLiveBalance()">
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-sm btn-link text-primary p-0" onclick="duplicateNewRow('${newRowId}', 'ledger')" title="Duplicate">
                            <i class="fa-solid fa-plus-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('${newRowId}').remove(); calculateLiveBalance();">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
            oldRow.parentNode.appendChild(tr);
        } else {
            const empName = oldRow.querySelector('input[name*="[employee_name]"]').value;
            const amount = oldRow.querySelector('input[name*="[amount]"]').value;
            tr.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-tie text-warning small"></i>
                        <input type="text" name="salaries[${newRowId}][employee_name]" value="${empName}" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" required>
                    </div>
                </td>
                <td class="text-end">
                    <input type="number" step="0.01" name="salaries[${newRowId}][amount]" value="${amount}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring salary-amount" required oninput="calculateLiveBalance()">
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-sm btn-link text-warning p-0" onclick="duplicateNewRow('${newRowId}', 'salary')" title="Duplicate">
                            <i class="fa-solid fa-plus-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('${newRowId}').remove(); calculateLiveBalance();">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
            oldRow.parentNode.appendChild(tr);
        }
        calculateLiveBalance();
    }

    function removeRow(model, id) {
        if(confirm('Are you sure you want to remove this entry?')) {
            const rowId = (model === 'ledger' ? 'ledger_row_' : 'salary_row_') + id;
            document.getElementById(rowId).remove();
            calculateLiveBalance();
        }
    }

    function deleteEntry(id, date) {
        Swal.fire({
            title: 'Delete Ledger for ' + date + '?',
            text: "This will reset all entries for this date to 0. You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('deleteForm');
                form.action = `/reports/daily-ledger/delete/${id}`;
                form.submit();
            }
        })
    }

    function showLedgerDetails(date, id) {
        fetch(`/reports/daily-ledger/details/${date}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalDateTitle').innerText = 'Ledger Details: ' + data.date;
                let incomeHtml = '';
                data.income.forEach(item => {
                    if(parseFloat(item.amount) !== 0) {
                        incomeHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-muted">${item.description}</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toFixed(2)}</span>
                        </li>`;
                    }
                });
                document.getElementById('modalIncomeList').innerHTML = incomeHtml || '<li class="list-group-item text-center text-muted small border-0">No income records</li>';
                document.getElementById('modalTotalIncome').innerText = 'LKR ' + parseFloat(data.total_income).toFixed(2);

                let expenseHtml = '';
                data.expense.forEach(item => {
                     if(parseFloat(item.amount) !== 0) {
                        expenseHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-muted">${item.description}</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toFixed(2)}</span>
                        </li>`;
                     }
                });
                data.salaries.forEach(item => {
                    if(parseFloat(item.amount) !== 0) {
                        expenseHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-warning"><i class="fa-solid fa-user-tie me-1"></i>${item.employee_name} (Salary)</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toFixed(2)}</span>
                        </li>`;
                    }
                });
                document.getElementById('modalExpenseList').innerHTML = expenseHtml || '<li class="list-group-item text-center text-muted small border-0">No expense records</li>';
                document.getElementById('modalTotalExpense').innerText = 'LKR ' + (parseFloat(data.total_expense) + parseFloat(data.total_salary)).toFixed(2);

                const modal = new bootstrap.Modal(document.getElementById('ledgerDetailsModal'));
                modal.show();
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateLiveBalance();
    });

    function resetForm() {
        window.location.href = "{{ route('reports.daily-ledger') }}";
    }
</script>

<style>
.focus-ring:focus { background-color: #fff; border-color: #6366f1; }
.border-bottom-dashed { border-bottom: 1px dashed #e5e7eb !important; }
.form-control-sm { font-size: 0.85rem; padding: 0.25rem 0.5rem; }
.table th { font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.btn-link { text-decoration: none; }
.btn-link:hover { opacity: 0.8; }
</style>
@endsection
