@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">3rd Party Cheque Tracking</h4>
            <p class="text-muted small">Monitor cheques that have been transferred to third parties.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'All 3rd Party', 'key' => 'all', 'icon' => 'fa-list', 'color' => '#64748b', 'bg' => '#f8fafc', 'status' => ''],
                ['label' => 'Sent', 'key' => 'received', 'icon' => 'fa-paper-plane', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'status' => 'received'],
                ['label' => 'Realized', 'key' => 'realized', 'icon' => 'fa-circle-check', 'color' => '#10b981', 'bg' => '#ecfdf5', 'status' => 'realized'],
                ['label' => 'Returned', 'key' => 'returned', 'icon' => 'fa-rotate-left', 'color' => '#ef4444', 'bg' => '#fef2f2', 'status' => 'returned'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('third-party-cheques.index', ['status' => $card['status']]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden card-stat" style="background: {{ $card['bg'] }};">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">{{ $card['label'] }}</div>
                                <div class="h4 fw-bold mb-0" style="color: {{ $card['color'] }};">{{ $stats[$card['key']]['count'] }}</div>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; background: #fff; color: {{ $card['color'] }};">
                                <i class="fa-solid {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: {{ $card['color'] }}33 !important;">
                            <span class="extra-small text-muted fw-bold">Total Amount</span>
                            <span class="small fw-bold" style="color: {{ $card['color'] }};">LKR {{ number_format($stats[$card['key']]['amount'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Table Section -->
    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="p-3 border-bottom bg-light bg-opacity-10">
            <form action="{{ route('third-party-cheques.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Search</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4 border-light rounded-3" placeholder="3rd Party name...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">3rd Party Name</label>
                    <input type="text" name="third_party_name" value="{{ request('third_party_name') }}" class="form-control form-control-sm border-light rounded-3" placeholder="Name...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Bank</label>
                    <select name="bank_id" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm border-light rounded-3">
                        <option value="">All Status</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Sent</option>
                        <option value="realized" {{ request('status') == 'realized' ? 'selected' : '' }}>Realized</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Transfer Date Range</label>
                    <input type="text" id="daterange-tp" class="form-control form-control-sm border-light rounded-3" placeholder="Select date range..." readonly>
                    <input type="hidden" name="from_date" id="from_date_tp" value="{{ request('from_date') }}">
                    <input type="hidden" name="to_date" id="to_date_tp" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3" style="background: #6366f1; border: none;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('third-party-cheques.index') }}" class="btn btn-light btn-sm px-3 rounded-3 border-light">
                            <i class="fa-solid fa-rotate-right me-1"></i> Clear
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-2 px-3 small fw-bold text-muted border-top pt-3">{{ $cheques->total() }} Results</div>
                </div>
            </form>
        </div>

        <script>
        $(function() {
            var start = moment('{{ request("from_date") }}' || null);
            var end = moment('{{ request("to_date") }}' || null);

            function cb(start, end) {
                if (start.isValid() && end.isValid()) {
                    $('#daterange-tp').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                    $('#from_date_tp').val(start.format('YYYY-MM-DD'));
                    $('#to_date_tp').val(end.format('YYYY-MM-DD'));
                } else {
                    $('#daterange-tp').val('');
                    $('#from_date_tp').val('');
                    $('#to_date_tp').val('');
                }
            }

            $('#daterange-tp').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'DD/MM/YYYY'
                }
            });

            $('#daterange-tp').on('apply.daterangepicker', function(ev, picker) {
                cb(picker.startDate, picker.endDate);
            });

            $('#daterange-tp').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#from_date_tp').val('');
                $('#to_date_tp').val('');
            });

            @if(request('from_date') && request('to_date'))
                cb(start, end);
            @endif
        });
        </script>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3 text-muted small text-uppercase">Type</th>
                        <th class="py-3 text-muted small text-uppercase">Transfer Date</th>
                        <th class="py-3 text-muted small text-uppercase">Cheq #</th>
                        <th class="py-3 text-muted small text-uppercase">Client</th>
                        <th class="py-3 text-muted small text-uppercase">3rd Party Name</th>
                        <th class="py-3 text-muted small text-uppercase text-end">Amount</th>
                        <th class="py-3 text-muted small text-uppercase text-center">Status</th>
                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    <tr>
                        <td class="ps-4">
                            <span class="badge rounded-pill px-2 py-1" style="background: #f5f3ff; color: #8b5cf6; font-size: 0.65rem;">
                                3RD
                            </span>
                        </td>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($cheque->transfer_date)->format('d/m/Y') }}</td>
                        <td class="small fw-bold">#{{ $cheque->inCheque->cheque_number }}</td>
                        <td class="small">{{ $cheque->inCheque->payer_name }}</td>
                        <td class="small fw-bold text-dark">{{ $cheque->third_party_name }}</td>
                        <td class="small fw-bold text-end">LKR {{ number_format($cheque->inCheque->amount, 2) }}</td>
                        <td class="text-center">
                            @php
                                $statusColors = [
                                    'received' => ['bg' => '#eff6ff', 'text' => '#3b82f6', 'label' => 'Sent'],
                                    'realized' => ['bg' => '#ecfdf5', 'text' => '#10b981', 'label' => 'Realized'],
                                    'returned' => ['bg' => '#fef2f2', 'text' => '#ef4444', 'label' => 'Returned'],
                                ];
                                $st = $statusColors[$cheque->status] ?? ['bg' => '#eee', 'text' => '#666', 'label' => $cheque->status];
                            @endphp
                            <div class="dropdown d-inline-block">
                                <span class="badge rounded-pill px-2 py-1 cursor-pointer dropdown-toggle" data-bs-toggle="dropdown" style="background: {{ $st['bg'] }}; color: {{ $st['text'] }}; font-size: 0.65rem;">
                                    {{ $st['label'] }}
                                </span>
                                <ul class="dropdown-menu shadow-sm border-light">
                                    <li>
                                        <form action="{{ route('third-party-cheques.update', $cheque) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="realized">
                                            <button type="submit" class="dropdown-item small">Mark as Realized</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('third-party-cheques.update', $cheque) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="returned">
                                            <button type="submit" class="dropdown-item small text-danger">Mark as Returned</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                             <form action="{{ route('third-party-cheques.destroy', $cheque) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
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
                        <td colspan="8" class="text-center py-5 text-muted small">No 3rd party transfers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-top">
            {{ $cheques->links() }}
        </div>
    </div>
</div>

<style>
    .card-stat { transition: all 0.2s ease-in-out; border: 1px solid transparent !important; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: rgba(99, 102, 241, 0.2) !important; }
    .btn-icon:hover { background: #f1f5f9; border-radius: 8px; }
    .extra-small { font-size: 0.7rem; }
</style>
@endsection
