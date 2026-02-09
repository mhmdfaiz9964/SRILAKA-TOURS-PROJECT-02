@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">Daily Ledger History</h4>
                <span class="text-muted small">Summary of all past daily ledgers</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.daily-ledger') }}"
                    class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2"
                    style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> Manual Entry
                </a>
                <div class="dropdown">
                    <button
                        class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle shadow-sm"
                        data-bs-toggle="dropdown" type="button">
                        <i class="fa-solid fa-file-export text-black"></i> Export History
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2"
                        style="min-width: 180px;">
                        <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3"
                            href="{{ route('reports.daily-ledger.history', array_merge(request()->all(), ['export' => 'excel'])) }}">
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                <i class="fa-solid fa-file-excel text-success"></i>
                            </div>
                            <span class="small fw-bold">Excel Format</span>
                        </a>
                        <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1"
                            href="{{ route('reports.daily-ledger.history', array_merge(request()->all(), ['export' => 'pdf'])) }}">
                            <div class="bg-danger bg-opacity-10 p-2 rounded-circle">
                                <i class="fa-solid fa-file-pdf text-danger"></i>
                            </div>
                            <span class="small fw-bold">PDF Format</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-4 shadow-sm border border-light mb-4">
            <div class="p-3 bg-light bg-opacity-10">
                <form action="{{ route('reports.daily-ledger.history') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                            class="form-control form-control-sm border-light rounded-3 shadow-none" placeholder="From Date">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                            class="form-control form-control-sm border-light rounded-3 shadow-none" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3 w-100"
                            style="background: #6366f1; border: none;">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-4 shadow-sm border border-light overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th class="ps-4 py-3 small text-uppercase text-muted border-0">Date</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end">Total Income</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end">Total Expense</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end">A/c Sales</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end">Bank Deposit</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="ps-4 fw-medium text-dark border-light">
                                    {{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}
                                </td>
                                <td class="text-end fw-bold text-success border-light">
                                    {{ number_format($record->total_income, 2) }}
                                </td>
                                <td class="text-end fw-bold text-danger border-light">
                                    {{ number_format($record->total_expense, 2) }}
                                </td>
                                <td class="text-end text-muted border-light">{{ number_format($record->ac_sales, 2) }}</td>
                                <td class="text-end text-muted border-light">{{ number_format($record->bank_deposit, 2) }}</td>
                                <td class="text-end pe-4 border-light">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button
                                            class="btn btn-sm btn-light border-0 text-primary bg-primary bg-opacity-10 rounded-pill px-3"
                                            onclick="viewDetails('{{ $record->date }}')" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a href="{{ route('reports.daily-ledger.edit', ['id' => $record->id]) }}"
                                            class="btn btn-sm btn-light border-0 text-success bg-success bg-opacity-10 rounded-pill px-3"
                                            title="Edit This Day">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button
                                            class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3"
                                            onclick="confirmDelete('{{ $record->date }}')" title="Delete This Day">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted small">No history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hidden Delete Form -->
        <form id="deleteForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <!-- SweetAlert Delete Confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(date) {
            Swal.fire({
                title: 'Delete Ledger for ' + date + '?',
                text: "This will remove all income, expenses, and salary entries for this day! This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // We need an ID to delete. DailyLedgerEntry.destroyDailyLedgerEntry expects an ID.
                    // But we want to delete by date. Let's find an ID for that date or update the controller.
                    // Actually, destroyDailyLedgerEntry finds the record by ID then deletes all for that date.
                    // We can fetch data first or just use a new "delete by date" route.

                    // For now, let's fix the controller to take a date or just use the details fetch to get an ID.
                    fetch(`/reports/daily-ledger/details/${date}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.income.length > 0 || data.expense.length > 0) {
                                const id = data.income[0] ? data.income[0].id : data.expense[0].id;
                                const form = document.getElementById('deleteForm');
                                form.action = `/reports/daily-ledger/delete/${id}`;
                                form.submit();
                            } else {
                                Swal.fire('Error', 'No entries found for this date', 'error');
                            }
                        });
                }
            })
        }
    </script>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="height: 80vh;">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalDate">Details</h5>
                        <span class="text-muted small">Daily Breakdown</span>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3 overflow-auto">
                    <div class="row g-4">
                        <!-- Income Column -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-success bg-opacity-10 h-100 rounded-4">
                                <div class="card-header bg-transparent border-0 fw-bold text-success">Income</div>
                                <div class="card-body p-0">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tbody id="incomeBody"></tbody>
                                        <tfoot class="border-top border-success">
                                            <tr>
                                                <td class="ps-3 fw-bold text-success">Total</td>
                                                <td class="text-end pe-3 fw-bold text-success" id="incomeTotal"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Expense Column -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-danger bg-opacity-10 h-100 rounded-4">
                                <div class="card-header bg-transparent border-0 fw-bold text-danger">Expenses</div>
                                <div class="card-body p-0">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tbody id="expenseBody"></tbody>
                                        <tfoot class="border-top border-danger">
                                            <tr>
                                                <td class="ps-3 fw-bold text-danger">Total</td>
                                                <td class="text-end pe-3 fw-bold text-danger" id="expenseTotal"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <a href="#" id="exportPdfBtn"
                        class="btn btn-sm btn-outline-danger rounded-3 gap-2 d-flex align-items-center">
                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                    </a>
                    <a href="#" id="editBtn" class="btn btn-sm btn-primary rounded-3 px-4"
                        style="background: #6366f1; border: none;">
                        Edit This Day
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(date) {
            fetch(`/reports/daily-ledger/details/${date}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalDate').innerText = `Details for ${data.date}`;

                    // Populate Income
                    let incomeHtml = '';
                    data.income.forEach(item => {
                        incomeHtml += `<tr><td class="ps-3 text-secondary">${item.description}</td><td class="text-end pe-3 fw-medium">${parseFloat(item.amount).toFixed(2)}</td></tr>`;
                    });
                    document.getElementById('incomeBody').innerHTML = incomeHtml;
                    document.getElementById('incomeTotal').innerText = data.total_income.toFixed(2);

                    // Populate Expense
                    let expenseHtml = '';
                    data.expense.forEach(item => {
                        expenseHtml += `<tr><td class="ps-3 text-secondary">${item.description}</td><td class="text-end pe-3 fw-medium">${parseFloat(item.amount).toFixed(2)}</td></tr>`;
                    });
                    document.getElementById('expenseBody').innerHTML = expenseHtml;
                    document.getElementById('expenseTotal').innerText = data.total_expense.toFixed(2);

                    // Update Links
                    document.getElementById('editBtn').href = `/reports/daily-ledger/edit/${data.income[0] ? data.income[0].id : data.expense[0].id}`;
                    document.getElementById('exportPdfBtn').href = `/reports/daily-ledger?date=${data.date}&export=pdf`;

                    // Show Modal
                    new bootstrap.Modal(document.getElementById('detailsModal')).show();
                });
        }
    </script>
@endsection