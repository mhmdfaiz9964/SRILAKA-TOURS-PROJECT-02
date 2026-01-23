@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">3rd Party Cheque Tracking</h4>
            <p class="text-muted small">Monitor cheques that have been transferred to third parties.</p>
        </div>
    </div>

    <div class="table-container bg-white rounded-4 shadow-sm border border-light overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-10 border-bottom">
                        <th class="ps-4 py-3 text-muted small text-uppercase">Transfer Date</th>
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
                        <td class="ps-4 small text-muted">{{ \Carbon\Carbon::parse($cheque->transfer_date)->format('d/m/Y') }}</td>
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
                        <td colspan="7" class="text-center py-5 text-muted small">No 3rd party transfers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
