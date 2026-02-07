@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Expenses</h4>
            <span class="text-muted small">Manage your daily expenses</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fa-solid fa-tags text-primary"></i> Categories
            </button>
            <button type="button" class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2" style="background: #6366f1; border: none;" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                <i class="fa-solid fa-plus"></i> Add Expense
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-danger small fw-bold text-uppercase mb-1">Total Expenses (Filtered)</div>
                        <div class="fw-bold text-danger fs-4">LKR {{ number_format($totalAmount, 2) }}</div>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                        <i class="fa-solid fa-money-bill-wave text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Export -->
    <div class="bg-white rounded-4 shadow-sm border border-light mb-4" style="z-index: 10;">
        <div class="p-3 bg-light bg-opacity-10">
            <form action="{{ route('expenses.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm border-light rounded-3 shadow-none" placeholder="Search reason, cheque...">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm border-light rounded-3 shadow-none">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select form-select-sm border-light rounded-3 shadow-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3 w-100" style="background: #6366f1; border: none;">Filter</button>
                </div>
                <div class="col-md-3 text-end">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle shadow-sm" data-bs-toggle="dropdown" type="button">
                            <i class="fa-solid fa-file-export text-black"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 180px;">
                            <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3" href="{{ route('expenses.index', array_merge(request()->all(), ['export' => 'excel'])) }}">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                    <i class="fa-solid fa-file-excel text-success"></i>
                                </div>
                                <span class="small fw-bold">Excel Format</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1" href="{{ route('expenses.index', array_merge(request()->all(), ['export' => 'pdf'])) }}">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-circle">
                                    <i class="fa-solid fa-file-pdf text-danger"></i>
                                </div>
                                <span class="small fw-bold">PDF Format</span>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-50">
                    <tr>
                        <th class="ps-4 py-3 small text-uppercase text-muted border-0">Date</th>
                        <th class="py-3 small text-uppercase text-muted border-0">Reason</th>
                        <th class="py-3 small text-uppercase text-muted border-0">Category</th>
                        <th class="py-3 small text-uppercase text-muted border-0 text-end">Amount</th>
                        <th class="py-3 small text-uppercase text-muted border-0">Paid By</th>
                        <th class="py-3 small text-uppercase text-muted border-0">Method</th>
                        <th class="py-3 small text-uppercase text-muted border-0">Details</th>
                        <th class="py-3 small text-uppercase text-muted border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="ps-4 small text-muted border-light">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="fw-medium text-dark border-light">{{ $expense->reason }}</td>
                        <td class="border-light">
                             @if($expense->category)
                                <span class="badge bg-light text-dark border fw-normal">{{ $expense->category->name }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="fw-bold text-danger border-light text-end">LKR {{ number_format($expense->amount, 2) }}</td>
                        <td class="small text-muted border-light">{{ $expense->paid_by ?? '-' }}</td>
                        <td class="border-light">
                            <span class="badge bg-light text-dark border fw-normal">{{ ucwords(str_replace('_', ' ', $expense->payment_method)) }}</span>
                        </td>
                        <td class="small text-muted border-light">
                            @if($expense->payment_method == 'cheque')
                                <div><span class="fw-bold">#{{ $expense->cheque_number }}</span></div>
                                <div style="font-size: 0.7rem;">{{ $expense->bank->name ?? '' }}</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end pe-4 border-light">
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon border-0 text-danger shadow-none">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">No expenses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $expenses->links() }}
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Add Category</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="addCategoryForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small text-muted">Category Name</label>
                        <input type="text" name="name" id="newCategoryName" class="form-control rounded-3 shadow-none" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm" style="background: #6366f1; border: none;">Save Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Expense Modal -->
<div class="modal fade" id="createExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Add New Expense</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <form action="{{ route('expenses.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Reason / Description</label>
                            <input type="text" name="reason" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light rounded-start-3">LKR</span>
                                <input type="number" step="0.01" name="amount" class="form-control border-start-0 ps-0 rounded-end-3 shadow-none" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Date</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Category</label>
                            <select name="category_id" class="form-select rounded-3 shadow-none">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Payment Method</label>
                            <select name="payment_method" id="paymentMethod" class="form-select rounded-3 shadow-none" onchange="toggleChequeFields()">
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                             <label class="form-label small fw-bold text-muted">Paid By (Optional)</label>
                             <input type="text" name="paid_by" class="form-control rounded-3 shadow-none" placeholder="e.g. John Doe">
                        </div>

                        <!-- Cheque Details -->
                        <div class="col-12" id="chequeDetails" style="display: none;">
                            <div class="p-3 bg-light rounded-3 mt-2">
                                <h6 class="small fw-bold text-muted mb-3">Cheque Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Cheque Number</label>
                                        <input type="text" name="cheque_number" class="form-control form-control-sm rounded-3 shadow-none" placeholder="6 Digits">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Cheque Date</label>
                                        <input type="date" name="cheque_date" class="form-control form-control-sm rounded-3 shadow-none">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Bank</label>
                                        <select name="bank_id" class="form-select form-select-sm rounded-3 shadow-none">
                                            <option value="">Select Bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Payer Name</label>
                                        <input type="text" name="payer_name" class="form-control form-control-sm rounded-3 shadow-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm" style="background: #6366f1; border: none;">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleChequeFields() {
        const method = document.getElementById('paymentMethod').value;
        const details = document.getElementById('chequeDetails');
        if (method === 'cheque') {
            details.style.display = 'block';
            details.querySelectorAll('input, select').forEach(el => el.required = true);
        } else {
            details.style.display = 'none';
            details.querySelectorAll('input, select').forEach(el => el.required = false);
        }
    }

    document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let name = document.getElementById('newCategoryName').value;
        let token = document.querySelector('input[name="_token"]').value;

        fetch('{{ route("expenses.categories.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh page to show new category in filters or just reload
                location.reload(); 
            } else {
                alert('Error adding category');
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
@endsection
