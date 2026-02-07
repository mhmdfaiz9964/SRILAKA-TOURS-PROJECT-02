@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header & Date Filter -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Daily Ledger</h4>
            <span class="text-muted small">Day Book / Cash Book (Manual Entry)</span>
        </div>
        <form action="{{ route('reports.daily-ledger') }}" method="GET" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control form-control-sm border-light rounded-3 shadow-none">
            <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">View</button>
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
        <!-- Opening Balance -->
        <div class="col-md-3 d-none">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f8fafc;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Opening Balance (B/F)</div>
                    <div class="fw-bold text-dark fs-5">LKR {{ number_format($openingBalance, 2) }}</div>
                    <small class="text-muted" style="font-size: 0.65rem;">Calculated from previous days</small>
                </div>
            </div>
        </div>
        <!-- Income -->
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                <div class="card-body p-3">
                    <div class="text-success small fw-bold text-uppercase mb-1">Total Daily Income</div>
                    <div class="fw-bold text-success fs-5">+ LKR {{ number_format($totalIncome, 2) }}</div>
                </div>
            </div>
        </div>
        <!-- Expenses -->
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                <div class="card-body p-3">
                    <div class="text-danger small fw-bold text-uppercase mb-1">Total Money Out</div>
                    <div class="fw-bold text-danger fs-5">- LKR {{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>
        </div>
        <!-- Closing Balance -->
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #eff6ff;">
                <div class="card-body p-3">
                    <div class="text-primary small fw-bold text-uppercase mb-1">Closing Balance</div>
                    <div class="fw-bold text-primary fs-5">LKR {{ number_format($closingBalance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('reports.daily-ledger.update') }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        
        <div class="row g-4 mb-4">
            <!-- Money In Section -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-arrow-down text-success small"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Income Entries</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-2 small text-muted">Description</th>
                                        <th class="py-2 small text-muted text-end pe-4">Amount (LKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incomeEntries as $entry)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $entry->description }}</td>
                                        <td class="text-end pe-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-success bg-opacity-10">
                                    <tr>
                                        <td class="ps-4 fw-bold text-success">Total</td>
                                        <td class="text-end fw-bold text-success pe-4">{{ number_format($totalIncome, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Money Out Section -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                         <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-arrow-up text-danger small"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Expense Entries</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-2 small text-muted">Description</th>
                                        <th class="py-2 small text-muted text-end pe-4">Amount (LKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenseEntries as $entry)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $entry->description }}</td>
                                        <td class="text-end pe-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-danger bg-opacity-10">
                                    <tr>
                                        <td class="ps-4 fw-bold text-danger">Total</td>
                                        <td class="text-end fw-bold text-danger pe-4">{{ number_format($totalExpenses, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-4 rounded-3 shadow-sm" style="background: #6366f1; border: none;">
                <i class="fa-solid fa-save me-2"></i> Save Changes
            </button>
        </div>
    </form>

    <!-- Created Ledger History Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="fw-bold mb-0">Created Ledger History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-10">
                        <tr>
                            <th class="ps-4 py-3 small text-muted text-uppercase">Date</th>
                            <th class="py-3 small text-muted text-end text-uppercase">In (Income)</th>
                            <th class="py-3 small text-muted text-end text-uppercase">Out (Expense)</th>
                             <th class="py-3 small text-muted text-end text-uppercase">Bank Dep.</th>
                            <th class="py-3 small text-muted text-end text-uppercase">A/c Sales</th>
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
                            <td class="text-end fw-medium text-muted">{{ number_format($entry->bank_deposit, 2) }}</td>
                            <td class="text-end fw-medium text-muted">{{ number_format($entry->ac_sales, 2) }}</td>
                             <td class="text-end fw-bold {{ $entry->total >= 0 ? 'text-success' : 'text-danger' }} pe-4">
                                {{ number_format($entry->total, 2) }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Show (Redirect to view detailed list or trigger modal) -->
                                    <button type="button" class="btn btn-sm btn-light border-0 text-primary bg-primary bg-opacity-10 rounded-pill px-3" 
                                            onclick="showLedgerDetails('{{ $entry->date }}', '{{ $entry->id }}')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                     <!-- Edit (Load Date into Form) -->
                                    <button type="button" class="btn btn-sm btn-light border-0 text-success bg-success bg-opacity-10 rounded-pill px-3" 
                                            onclick="window.location='{{ route('reports.daily-ledger', ['date' => $entry->date]) }}'">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <!-- Delete -->
                                    <button type="button" class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3" 
                                            onclick="deleteEntry({{ $entry->id }}, '{{ $entry->date }}')">
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
                        <ul class="list-group list-group-flush" id="modalIncomeList">
                            <!-- Items populated via JS -->
                        </ul>
                         <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                            <span>Total Income</span>
                            <span class="text-success" id="modalTotalIncome">0.00</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-danger mb-3 border-bottom pb-2">Expenses</h6>
                        <ul class="list-group list-group-flush" id="modalExpenseList">
                            <!-- Items populated via JS -->
                        </ul>
                         <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                            <span>Total Expense</span>
                            <span class="text-danger" id="modalTotalExpense">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                 <a href="#" id="modalExportPdf" class="btn btn-sm btn-danger rounded-pill px-4">
                    <i class="fa-solid fa-file-pdf me-2"></i> PDF
                </a>
                 <a href="#" id="modalExportExcel" class="btn btn-sm btn-success rounded-pill px-4">
                    <i class="fa-solid fa-file-excel me-2"></i> Excel
                </a>
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
    // Just direct link navigation used for 'Edit', no JS function needed other than confirm delete and show details

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
                // The route handles deletion by ID (which calls destroyDailyLedgerEntry)
                // We're passing the representative ID of the ledger entry
                form.action = `/reports/daily-ledger/delete/${id}`;
                form.submit();
            }
        })
    }

    function showLedgerDetails(date, id) {
        // Fetch details via AJAX or simply rely on the fact that we can generate check links
        // Ideally, we fetch the data to show in the modal.
        // We need a route for fetching details JSON
        
        fetch(`/reports/daily-ledger/details/${date}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalDateTitle').innerText = 'Ledger Details: ' + data.date;
                
                // Populate Income
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

                // Populate Expense
                let expenseHtml = '';
                data.expense.forEach(item => {
                     if(parseFloat(item.amount) !== 0) {
                        expenseHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-muted">${item.description}</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toFixed(2)}</span>
                        </li>`;
                     }
                });
                document.getElementById('modalExpenseList').innerHTML = expenseHtml || '<li class="list-group-item text-center text-muted small border-0">No expense records</li>';
                 document.getElementById('modalTotalExpense').innerText = 'LKR ' + parseFloat(data.total_expense).toFixed(2);

                 // Update Export Links
                 document.getElementById('modalExportPdf').href = `/reports/daily-ledger?date=${date}&export=pdf`;
                 document.getElementById('modalExportExcel').href = `/reports/daily-ledger?date=${date}&export=excel`;

                const modal = new bootstrap.Modal(document.getElementById('ledgerDetailsModal'));
                modal.show();
            });
    }
</script>

<style>
.focus-ring:focus {
    background-color: #fff;
    border-color: #6366f1;
}
.border-bottom-dashed {
    border-bottom: 1px dashed #e5e7eb !important;
}
</style>
@endsection
