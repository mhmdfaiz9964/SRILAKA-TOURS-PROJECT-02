@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('reports.balance-sheet.history') }}" class="btn btn-light rounded-circle shadow-sm"
                    title="Back to History">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-0">Balance Sheet Entry</h4>
                    <span class="text-muted small">Financial Position for: <span
                            class="text-primary fw-bold">{{ $date->format('d M, Y') }}</span></span>
                </div>
                <a href="{{ route('reports.balance-sheet') }}"
                    class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm ms-2"
                    style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-plus me-1"></i> Create New
                </a>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('reports.balance-sheet') }}" method="GET" class="d-flex gap-2">
                    <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                        class="form-control form-control-sm border-light rounded-3 shadow-none">
                    <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3"
                        style="background: #6366f1; border: none;">View</button>
                </form>
            </div>
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
                        <div class="fw-bold text-success fs-5">LKR <span
                                id="summaryTotalAssets">{{ number_format($totalAssets, 2) }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #fef2f2;">
                    <div class="card-body p-3">
                        <div class="text-danger small fw-bold text-uppercase mb-1">Total Liabilities & Equity</div>
                        <div class="fw-bold text-danger fs-5">LKR <span
                                id="summaryTotalLiabEq">{{ number_format($totalLiabilitiesAndEquity, 2) }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @php $diff = $totalAssets - $totalLiabilitiesAndEquity; @endphp
                <div id="diffCard" class="card border-0 shadow-sm rounded-4 h-100"
                    style="{{ abs($diff) < 0.01 ? 'background: #eff6ff;' : 'background: #fff1f2;' }}">
                    <div class="card-body p-3">
                        <div id="diffTitle"
                            class="{{ abs($diff) < 0.01 ? 'text-primary' : 'text-danger' }} small fw-bold text-uppercase mb-1">
                            Difference</div>
                        <div id="diffValue" class="fw-bold {{ abs($diff) < 0.01 ? 'text-primary' : 'text-danger' }} fs-5">
                            LKR <span>{{ number_format($diff, 2) }}</span>
                            <i
                                class="fa-solid {{ abs($diff) < 0.01 ? 'fa-check-circle' : 'fa-circle-exclamation' }} ms-1"></i>
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
                        <div
                            class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-building-columns text-success small"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Assets</h6>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border-0 text-success rounded-circle"
                                onclick="addRow('asset')" title="Add Asset">
                                <i class="fa-solid fa-plus-circle fa-lg"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="asset-table">
                                    <thead class="bg-light bg-opacity-10">
                                        <tr>
                                            <th class="ps-4 py-2 small text-muted">Name</th>
                                            <th class="py-2 small text-muted text-end" style="width: 150px;">Value (LKR)
                                            </th>
                                            <th class="py-2 small text-muted text-center" style="width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assets as $entry)
                                            <tr class="entry-row" data-category="asset">
                                                <td class="ps-4">
                                                    <input type="hidden" name="entries[{{ $entry->id }}][id]"
                                                        value="{{ $entry->id }}">
                                                    <input type="hidden" name="entries[{{ $entry->id }}][category]"
                                                        value="asset">
                                                    <input type="text" name="entries[{{ $entry->id }}][name]"
                                                        value="{{ $entry->name }}"
                                                        class="form-control form-control-sm border-0 bg-transparent fw-medium text-dark shadow-none p-0">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]"
                                                        value="{{ $entry->amount }}"
                                                        class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring amount-input"
                                                        oninput="calculateBS()">
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon text-success p-0 border-0 shadow-none"
                                                            onclick="duplicateRow(this)" title="Duplicate">
                                                            <i class="fa-solid fa-plus-circle"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon text-danger p-0 border-0 shadow-none"
                                                            onclick="removeRow(this)" title="Delete">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-success bg-opacity-10">
                                        <tr>
                                            <td class="ps-4 fw-bold text-success">Total Assets</td>
                                            <td class="text-end fw-bold text-success" id="totalAssetsDisplay">
                                                {{ number_format($totalAssets, 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liabilities & Equity Section -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div
                            class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-scale-balanced text-danger small"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Liabilities & Equity</h6>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-light border-0 text-danger rounded-pill px-2"
                                    onclick="addRow('liability')" title="Add Liability">
                                    <i class="fa-solid fa-plus me-1"></i> Liab
                                </button>
                                <button type="button" class="btn btn-sm btn-light border-0 text-primary rounded-pill px-2"
                                    onclick="addRow('equity')" title="Add Equity">
                                    <i class="fa-solid fa-plus me-1"></i> Equity
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="liab-eq-table">
                                    <thead class="bg-light bg-opacity-10">
                                        <tr>
                                            <th class="ps-4 py-2 small text-muted">Name</th>
                                            <th class="py-2 small text-muted text-end" style="width: 150px;">Value (LKR)
                                            </th>
                                            <th class="py-2 small text-muted text-center" style="width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Combined List (UI handles categories via data attributes) -->
                                        @foreach($liabilities as $entry)
                                            <tr class="entry-row" data-category="liability">
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span
                                                            class="badge bg-danger bg-opacity-10 text-danger extra-small py-0">L</span>
                                                        <input type="hidden" name="entries[{{ $entry->id }}][id]"
                                                            value="{{ $entry->id }}">
                                                        <input type="hidden" name="entries[{{ $entry->id }}][category]"
                                                            value="liability">
                                                        <input type="text" name="entries[{{ $entry->id }}][name]"
                                                            value="{{ $entry->name }}"
                                                            class="form-control form-control-sm border-0 bg-transparent fw-medium text-dark shadow-none p-0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]"
                                                        value="{{ $entry->amount }}"
                                                        class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring amount-input"
                                                        oninput="calculateBS()">
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon text-danger p-0 border-0 shadow-none"
                                                            onclick="duplicateRow(this)" title="Duplicate">
                                                            <i class="fa-solid fa-plus-circle"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon text-danger p-0 border-0 shadow-none"
                                                            onclick="removeRow(this)" title="Delete">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @foreach($equity as $entry)
                                            <tr class="entry-row" data-category="equity">
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span
                                                            class="badge bg-primary bg-opacity-10 text-primary extra-small py-0">E</span>
                                                        <input type="hidden" name="entries[{{ $entry->id }}][id]"
                                                            value="{{ $entry->id }}">
                                                        <input type="hidden" name="entries[{{ $entry->id }}][category]"
                                                            value="equity">
                                                        <input type="text" name="entries[{{ $entry->id }}][name]"
                                                            value="{{ $entry->name }}"
                                                            class="form-control form-control-sm border-0 bg-transparent fw-medium text-dark shadow-none p-0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="entries[{{ $entry->id }}][amount]"
                                                        value="{{ $entry->amount }}"
                                                        class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring amount-input"
                                                        oninput="calculateBS()">
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon text-primary p-0 border-0 shadow-none"
                                                            onclick="duplicateRow(this)" title="Duplicate">
                                                            <i class="fa-solid fa-plus-circle"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon text-danger p-0 border-0 shadow-none"
                                                            onclick="removeRow(this)" title="Delete">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-danger bg-opacity-10">
                                        <tr>
                                            <td class="ps-4 fw-bold text-danger">Total Liab + Equity</td>
                                            <td class="text-end fw-bold text-danger" id="totalLiabEqDisplay">
                                                {{ number_format($totalLiabilitiesAndEquity, 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <button type="submit" class="btn btn-primary px-4 rounded-3 shadow-sm"
                    style="background: #6366f1; border: none;">
                    <i class="fa-solid fa-save me-2"></i> Save Balance Sheet
                </button>
            </div>
        </form>

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
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0">Balance Sheet History Summary</h6>
            </div>
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
                                    {{ \Carbon\Carbon::parse($entry->date)->format('d M, Y') }}
                                </td>
                                <td class="text-end fw-bold text-success border-light">
                                    {{ number_format($entry->total_assets, 2) }}
                                </td>
                                <td class="text-end fw-bold text-danger border-light">
                                    {{ number_format($entry->total_liab_eq, 2) }}
                                </td>
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
                                        <a href="{{ route('reports.balance-sheet.edit', ['id' => $entry->id]) }}"
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
                    <a href="#" id="modalEditBtn" class="btn btn-sm btn-primary rounded-pill px-4"
                        style="background: #6366f1; border: none;">Edit Entry</a>
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
        let newEntryIndex = Date.now();

        function addRow(category) {
            const tableId = category === 'asset' ? 'asset-table' : 'liab-eq-table';
            const tbody = document.getElementById(tableId).querySelector('tbody');
            const index = 'new_' + newEntryIndex++;

            const badge = category === 'asset' ? '' :
                (category === 'liability' ? '<span class="badge bg-danger bg-opacity-10 text-danger extra-small py-0">L</span>' :
                    '<span class="badge bg-primary bg-opacity-10 text-primary extra-small py-0">E</span>');

            const row = document.createElement('tr');
            row.className = 'entry-row';
            row.dataset.category = category;
            row.innerHTML = `
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            ${badge}
                            <input type="hidden" name="entries[${index}][category]" value="${category}">
                            <input type="text" name="entries[${index}][name]" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" placeholder="New ${category}..." required>
                        </div>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="entries[${index}][amount]" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring amount-input" value="0" oninput="calculateBS()">
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                `;
            tbody.appendChild(row);
            calculateBS();
        }

        function duplicateRow(btn) {
            const sourceRow = btn.closest('tr');
            const category = sourceRow.dataset.category;
            const nameInput = sourceRow.querySelector('input[type="text"]');
            const amountInput = sourceRow.querySelector('.amount-input');

            const tableId = category === 'asset' ? 'asset-table' : 'liab-eq-table';
            const tbody = document.getElementById(tableId).querySelector('tbody');
            const index = 'new_' + newEntryIndex++;

            const badge = category === 'asset' ? '' :
                (category === 'liability' ? '<span class="badge bg-danger bg-opacity-10 text-danger extra-small py-0">L</span>' :
                    '<span class="badge bg-primary bg-opacity-10 text-primary extra-small py-0">E</span>');

            const row = document.createElement('tr');
            row.className = 'entry-row';
            row.dataset.category = category;
            row.innerHTML = `
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    ${badge}
                                    <input type="hidden" name="entries[${index}][category]" value="${category}">
                                    <input type="text" name="entries[${index}][name]" value="${nameInput.value}" class="form-control form-control-sm border-light bg-light rounded-3 shadow-none focus-ring px-2" required>
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="entries[${index}][amount]" value="${amountInput.value}" class="form-control form-control-sm text-end fw-bold border-light bg-light rounded-3 shadow-none focus-ring amount-input" oninput="calculateBS()">
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        `;
            sourceRow.after(row);
            calculateBS();
        }

        function removeRow(btn) {
            if (confirm('Remove this entry?')) {
                btn.closest('tr').remove();
                calculateBS();
            }
        }

        function calculateBS() {
            let totalAssets = 0;
            let totalLiabEq = 0;

            document.querySelectorAll('.entry-row').forEach(row => {
                const amountField = row.querySelector('.amount-input');
                const amount = parseFloat(amountField.value) || 0;
                if (row.dataset.category === 'asset') {
                    totalAssets += amount;
                } else {
                    totalLiabEq += amount;
                }
            });

            // Update displays
            document.getElementById('totalAssetsDisplay').innerText = totalAssets.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('totalLiabEqDisplay').innerText = totalLiabEq.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('summaryTotalAssets').innerText = totalAssets.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('summaryTotalLiabEq').innerText = totalLiabEq.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const diff = totalAssets - totalLiabEq;
            const diffSpan = document.querySelector('#diffValue span');
            diffSpan.innerText = diff.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const diffCard = document.getElementById('diffCard');
            const diffTitle = document.getElementById('diffTitle');
            const diffValue = document.getElementById('diffValue');
            const diffIcon = diffValue.querySelector('i');

            if (Math.abs(diff) < 0.01) {
                diffCard.style.background = '#eff6ff';
                diffTitle.className = 'text-primary small fw-bold text-uppercase mb-1';
                diffValue.className = 'fw-bold text-primary fs-5';
                diffIcon.className = 'fa-solid fa-check-circle ms-1';
            } else {
                diffCard.style.background = '#fff1f2';
                diffTitle.className = 'text-danger small fw-bold text-uppercase mb-1';
                diffValue.className = 'fw-bold text-danger fs-5';
                diffIcon.className = 'fa-solid fa-circle-exclamation ms-1';
            }
        }

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

                    // Set edit button link if data exists
                    if (data.assets && data.assets.length > 0) {
                        document.getElementById('modalEditBtn').href = `/reports/balance-sheet/edit/${data.assets[0].id}`;
                        document.getElementById('modalEditBtn').style.display = 'inline-block';
                    } else if (data.liabilities && data.liabilities.length > 0) {
                        document.getElementById('modalEditBtn').href = `/reports/balance-sheet/edit/${data.liabilities[0].id}`;
                        document.getElementById('modalEditBtn').style.display = 'inline-block';
                    } else {
                        document.getElementById('modalEditBtn').style.display = 'none';
                    }

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
        .focus-ring:focus {
            background-color: #fff;
            border-color: #6366f1;
        }

        .extra-small {
            font-size: 0.6rem;
        }

        .btn-link {
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .btn-link:hover {
            opacity: 0.7;
        }

        .border-bottom-dashed {
            border-bottom: 1px dashed #e5e7eb !important;
        }
    </style>
@endsection