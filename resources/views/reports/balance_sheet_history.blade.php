@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">Balance Sheet History</h4>
                <span class="text-muted small">Summary of all past balance sheets</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.balance-sheet') }}"
                    class="btn btn-primary btn-sm px-4 rounded-3 d-flex align-items-center gap-2"
                    style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus"></i> New Sheet
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
                            href="{{ route('reports.balance-sheet.history', array_merge(request()->all(), ['export' => 'excel'])) }}">
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                <i class="fa-solid fa-file-excel text-success"></i>
                            </div>
                            <span class="small fw-bold">Excel Format</span>
                        </a>
                        <a class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 mt-1"
                            href="{{ route('reports.balance-sheet.history', array_merge(request()->all(), ['export' => 'pdf'])) }}">
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
                <form action="{{ route('reports.balance-sheet.history') }}" method="GET" class="row g-2 align-items-center">
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

        <!-- History Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #f0fdf4;">
                    <div class="card-body p-3 text-center">
                        <div class="text-success small fw-bold text-uppercase mb-1">Total History Assets</div>
                        <div class="fw-bold text-success fs-5">LKR {{ number_format($historySummary['total_assets'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                    <div class="card-body p-3 text-center">
                        <div class="text-danger small fw-bold text-uppercase mb-1">Total History Liab + Equity</div>
                        <div class="fw-bold text-danger fs-5">LKR {{ number_format($historySummary['total_liab_eq'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th class="ps-4 py-3 small text-uppercase text-muted border-0">Date</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end">Assets</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end">Liab + Equity</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end pe-4">Difference</th>
                            <th class="py-3 small text-uppercase text-muted border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bsHistory as $entry)
                            <tr>
                                <td class="ps-4 fw-medium text-dark border-light">
                                    {{ \Carbon\Carbon::parse($entry->date)->format('d M, Y') }}</td>
                                <td class="text-end fw-bold text-success border-light">
                                    {{ number_format($entry->total_assets, 2) }}</td>
                                <td class="text-end fw-bold text-danger border-light">
                                    {{ number_format($entry->total_liab_eq, 2) }}</td>
                                <td
                                    class="text-end fw-bold {{ abs($entry->difference) < 0.01 ? 'text-primary' : 'text-danger' }} pe-4 border-light">
                                    {{ number_format($entry->difference, 2) }}
                                    <i
                                        class="fa-solid {{ abs($entry->difference) < 0.01 ? 'fa-check-circle' : 'fa-circle-exclamation' }} ms-1"></i>
                                </td>
                                <td class="text-end pe-4 border-light">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button"
                                            class="btn btn-sm btn-light border-0 text-primary bg-primary bg-opacity-10 rounded-pill px-3"
                                            onclick="showBSDetails('{{ $entry->date }}')" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a href="{{ route('reports.balance-sheet', ['date' => $entry->date]) }}" target="_blank"
                                            class="btn btn-sm btn-light border-0 text-success bg-success bg-opacity-10 rounded-pill px-3"
                                            title="Edit This Day">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-sm btn-light border-0 text-danger bg-danger bg-opacity-10 rounded-pill px-3"
                                            onclick="deleteBS({{ $entry->id }}, '{{ $entry->date }}')" title="Delete This Day">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small">No history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="bsDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalDateTitle">Balance Sheet Details</h5>
                        <p class="text-muted small mb-0">Financial Position Summary</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-success mb-3 border-bottom pb-2">Assets</h6>
                            <ul class="list-group list-group-flush" id="modalAssetsList"></ul>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                                <span>Total Assets</span>
                                <span class="text-success" id="modalTotalAssets">0.00</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger mb-3 border-bottom pb-2">Liabilities & Equity</h6>
                            <ul class="list-group list-group-flush" id="modalLiabEqList"></ul>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                                <span>Total Liab + Eq</span>
                                <span class="text-danger" id="modalTotalLiabEq">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showBSDetails(date) {
            fetch(`/reports/balance-sheet/details/${date}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalDateTitle').innerText = 'Balance Sheet: ' + data.date;

                    let assetsHtml = '';
                    data.assets.forEach(item => {
                        assetsHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-muted">${item.name}</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                        </li>`;
                    });
                    document.getElementById('modalAssetsList').innerHTML = assetsHtml || '<li class="list-group-item text-center text-muted small border-0">No assets records</li>';
                    document.getElementById('modalTotalAssets').innerText = 'LKR ' + parseFloat(data.total_assets).toLocaleString(undefined, { minimumFractionDigits: 2 });

                    let liabEqHtml = '';
                    data.liabilities.forEach(item => {
                        liabEqHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-danger"><span class="badge bg-danger-subtle text-danger me-1">L</span>${item.name}</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                        </li>`;
                    });
                    data.equity.forEach(item => {
                        liabEqHtml += `<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-dashed">
                            <span class="small text-primary"><span class="badge bg-primary-subtle text-primary me-1">E</span>${item.name}</span>
                            <span class="fw-bold text-dark">${parseFloat(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                        </li>`;
                    });
                    document.getElementById('modalLiabEqList').innerHTML = liabEqHtml || '<li class="list-group-item text-center text-muted small border-0">No records</li>';
                    document.getElementById('modalTotalLiabEq').innerText = 'LKR ' + parseFloat(data.total_liab_eq).toLocaleString(undefined, { minimumFractionDigits: 2 });

                    const modal = new bootstrap.Modal(document.getElementById('bsDetailsModal'));
                    modal.show();
                });
        }

        function deleteBS(id, date) {
            Swal.fire({
                title: 'Delete Balance Sheet for ' + date + '?',
                text: "This will remove all records for this date. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('deleteForm');
                    form.action = `/reports/balance-sheet/delete/${id}`;
                    form.submit();
                }
            })
        }
    </script>

    <style>
        .border-bottom-dashed {
            border-bottom: 1px dashed #e5e7eb !important;
        }
    </style>
@endsection