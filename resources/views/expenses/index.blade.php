@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Expenses</h4>
            <span class="text-muted small">Manage your daily expenses</span>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2" style="background: #6366f1; border: none;">
            <i class="fa-solid fa-plus"></i> Add Expense
        </a>
    </div>

    <div class="bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ route('expenses.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm border-light rounded-3" placeholder="Search reason, cheque number...">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm border-light rounded-3">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3 w-100" style="background: #6366f1; border: none;">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-10">
                    <tr>
                        <th class="ps-4 py-3 small text-uppercase text-muted">Date</th>
                        <th class="py-3 small text-uppercase text-muted">Reason</th>
                        <th class="py-3 small text-uppercase text-muted">Amount</th>
                        <th class="py-3 small text-uppercase text-muted">Paid By</th>
                        <th class="py-3 small text-uppercase text-muted">Method</th>
                        <th class="py-3 small text-uppercase text-muted">Cheque Details</th>
                        <th class="py-3 small text-uppercase text-muted text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="ps-4 small text-muted">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="fw-medium text-dark">{{ $expense->reason }}</td>
                        <td class="fw-bold text-dark">LKR {{ number_format($expense->amount, 2) }}</td>
                        <td class="small text-muted">{{ $expense->paid_by ?? '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border fw-normal">{{ ucwords(str_replace('_', ' ', $expense->payment_method)) }}</span>
                        </td>
                        <td class="small text-muted">
                            @if($expense->payment_method == 'cheque')
                                <div><span class="fw-bold">#{{ $expense->cheque_number }}</span></div>
                                <div style="font-size: 0.7rem;">{{ $expense->bank->name ?? 'Unknown Bank' }}</div>
                                <div style="font-size: 0.7rem;">{{ $expense->cheque_date ? $expense->cheque_date->format('d/m/Y') : '' }}</div>
                                <div style="font-size: 0.7rem;">{{ $expense->payer_name }}</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end pe-4">
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
                        <td colspan="7" class="text-center py-5 text-muted small">No expenses found.</td>
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
@endsection
