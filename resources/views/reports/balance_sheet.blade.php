@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Balance Sheet</h4>
            <span class="text-muted small">Financial Position (Manual Entry)</span>
        </div>
        <form action="{{ route('reports.balance-sheet') }}" method="GET" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control form-control-sm border-light rounded-3 shadow-none">
            <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">View</button>
            <div class="dropdown">
                <button class="btn btn-white btn-sm px-3 border-light rounded-3 d-flex align-items-center gap-2 dropdown-toggle shadow-sm" data-bs-toggle="dropdown" type="button">
                    <i class="fa-solid fa-file-export text-black"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 180px;">
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3" href="{{ route('reports.balance-sheet', ['date' => $date->format('Y-m-d'), 'export' => 'excel']) }}">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                            <i class="fa-solid fa-file-excel text-success"></i>
                        </div>
                        <span class="small fw-bold">Excel Format</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1" href="{{ route('reports.balance-sheet', ['date' => $date->format('Y-m-d'), 'export' => 'pdf']) }}">
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
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                <div class="card-body p-3">
                    <div class="text-success small fw-bold text-uppercase mb-1">Total Assets</div>
                    <div class="fw-bold text-success fs-5">LKR {{ number_format($totalAssets, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                <div class="card-body p-3">
                    <div class="text-danger small fw-bold text-uppercase mb-1">Total Liabilities & Equity</div>
                    <div class="fw-bold text-danger fs-5">LKR {{ number_format($totalLiabilitiesAndEquity, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php $diff = $totalAssets - $totalLiabilitiesAndEquity; @endphp
             <div class="card border-0 shadow-sm rounded-4 h-100" style="{{ abs($diff) < 0.01 ? 'background: #eff6ff;' : 'background: #fff1f2;' }}">
                <div class="card-body p-3">
                    <div class="{{ abs($diff) < 0.01 ? 'text-primary' : 'text-danger' }} small fw-bold text-uppercase mb-1">Difference</div>
                    <div class="fw-bold {{ abs($diff) < 0.01 ? 'text-primary' : 'text-danger' }} fs-5">
                        LKR {{ number_format($diff, 2) }}
                        @if(abs($diff) < 0.01) <i class="fa-solid fa-check-circle ms-1"></i> @else <i class="fa-solid fa-circle-exclamation ms-1"></i> @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('reports.balance-sheet.update') }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

        <div class="row g-4 mb-4">
            <!-- Assets Section -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-building-columns text-success small"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Assets</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="asset-table">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-2 small text-muted">Name</th>
                                        <th class="py-2 small text-muted text-end pe-4">Value (LKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assets as $entry)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $entry->name }}</td>
                                        <td class="text-end pe-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-success bg-opacity-10">
                                    <tr>
                                        <td class="ps-4 fw-bold text-success">Total Assets</td>
                                        <td class="text-end fw-bold text-success pe-4">{{ number_format($totalAssets, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="p-3 text-center border-top">
                                <button type="button" class="btn btn-sm btn-light border-0 text-success bg-success bg-opacity-10 rounded-pill px-3" onclick="addRow('asset')">
                                    <i class="fa-solid fa-plus me-1"></i> Add Asset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liabilities & Equity Section -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                         <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-scale-balanced text-danger small"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Liabilities & Equity</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-break" id="liability-equity-table">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-2 small text-muted">Name</th>
                                        <th class="py-2 small text-muted text-end pe-4">Value (LKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Liabilities -->
                                    <tr class="bg-light">
                                        <td colspan="2" class="ps-4 py-1 small fw-bold text-muted text-uppercase">Liabilities</td>
                                    </tr>
                                    @foreach($liabilities as $entry)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $entry->name }}</td>
                                        <td class="text-end pe-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring">
                                        </td>
                                    </tr>
                                    @endforeach

                                    <!-- Equity -->
                                    <tr class="bg-light">
                                        <td colspan="2" class="ps-4 py-1 small fw-bold text-muted text-uppercase">Equity</td>
                                    </tr>
                                    @foreach($equity as $entry)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $entry->name }}</td>
                                        <td class="text-end pe-4">
                                            <input type="hidden" name="entries[{{ $entry->id }}][id]" value="{{ $entry->id }}">
                                            <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]" value="{{ $entry->amount }}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-danger bg-opacity-10">
                                    <tr>
                                        <td class="ps-4 fw-bold text-danger">Total Liab + Equity</td>
                                        <td class="text-end fw-bold text-danger pe-4">{{ number_format($totalLiabilitiesAndEquity, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="p-3 text-center border-top">
                                <button type="button" class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3 me-2" onclick="addRow('liability')">
                                    <i class="fa-solid fa-plus me-1"></i> Add Liability
                                </button>
                                <button type="button" class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3" onclick="addRow('equity')">
                                    <i class="fa-solid fa-plus me-1"></i> Add Equity
                                </button>
                            </div>
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
</div>

<script>
    let newEntryIndex = 0;

    function addRow(type) {
        let tableId = type === 'asset' ? 'asset-table' : 'liability-equity-table';
        const table = document.getElementById(tableId).querySelector('tbody');
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td class="ps-4">
                <input type="text" name="new_entries[${newEntryIndex}][name]" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring" placeholder="Item Name" required>
                <input type="hidden" name="new_entries[${newEntryIndex}][category]" value="${type}">
            </td>
            <td class="text-end pe-4">
                <input type="number" step="0.01" name="new_entries[${newEntryIndex}][amount]" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring" placeholder="0.00" required>
            </td>
        `;

        table.appendChild(row);
        newEntryIndex++;
    }
</script>

<style>
.focus-ring:focus {
    background-color: #fff;
    border-color: #6366f1;
}
</style>
@endsection
