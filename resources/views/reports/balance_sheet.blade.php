@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Balance Sheet</h4>
            <span class="text-muted small">Financial Position (End of Day)</span>
        </div>
        <form action="{{ route('reports.balance-sheet') }}" method="GET" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control form-control-sm border-light rounded-3 shadow-none">
            <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">View</button>
        </form>
    </div>

    <div class="row g-4">
        <!-- Assets -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                     <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-building-columns text-primary small"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Assets (What we have)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                             <thead class="bg-light bg-opacity-10">
                                <tr>
                                    <th class="ps-4 py-2 small text-muted">Asset Name</th>
                                    <th class="py-2 small text-muted text-end pe-4">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Customer Outstanding</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($customerOutstanding, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Cheques in Hand</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($chequesInHand, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Returned cheques</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($returnedCheques, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Stock at Cost</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($stockAtCost, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Other Assets</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR 0.00</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-primary bg-opacity-10">
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">Total Assets</td>
                                    <td class="pe-4 text-end fw-bold text-primary">LKR {{ number_format($totalAssets, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liabilities & Equity -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex align-items-center gap-2">
                     <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-scale-balanced text-warning small"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Liabilities & Equity (Calculated)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                         <table class="table table-hover align-middle mb-0">
                             <thead class="bg-light bg-opacity-10">
                                <tr>
                                    <th class="ps-4 py-2 small text-muted">Liability / Equity Name</th>
                                    <th class="py-2 small text-muted text-end pe-4">Deserved Value</th>
                                </tr>
                            </thead>
                             <tbody>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Supplier Outstanding</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($supplierOutstanding, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Investors / Capital</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR {{ number_format($investors, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Other Liabilities</td>
                                    <td class="text-end pe-4 fw-bold text-dark">LKR 0.00</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">Profit / Loss (Balancing Figure)</td>
                                    <td class="text-end pe-4 fw-bold {{ $profitOrLoss >= 0 ? 'text-success' : 'text-danger' }}">
                                        LKR {{ number_format($profitOrLoss, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-warning bg-opacity-10">
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">Total Liabilities + Equity</td>
                                    <td class="pe-4 text-end fw-bold text-dark">LKR {{ number_format($totalLiabilitiesAndEquity, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accounting Equation Check -->
    <div class="mt-4 text-center">
        <div class="d-inline-flex align-items-center gap-3 px-4 py-2 rounded-pill bg-white shadow-sm border">
            <span class="fw-bold text-primary">Assets ({{ number_format($totalAssets, 2) }})</span>
            <i class="fa-solid fa-equals text-muted"></i>
            <span class="fw-bold text-dark">Liabilities & Equity ({{ number_format($totalLiabilitiesAndEquity, 2) }})</span>
            @if(abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01)
                <i class="fa-solid fa-circle-check text-success ms-2"></i>
            @else
                <i class="fa-solid fa-circle-exclamation text-danger ms-2" title="Mismatch"></i>
            @endif
        </div>
    </div>
</div>
@endsection
