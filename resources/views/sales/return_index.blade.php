@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-0 fw-bold">Sales Returns</h4>
                    <p class="text-muted small mb-0">History of all product returns</p>
                </div>
                <a href="{{ route('sales.index') }}"
                    class="btn btn-outline-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="fa-solid fa-cart-shopping"></i> Back to Sales
                </a>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                                <tr>
                                    <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase">Date</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Return #</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Customer</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Total Returned</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Cash Backed</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Notes</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td class="ps-4 text-muted small">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</td>
                                        <td class="fw-bold text-dark small">{{ $sale->invoice_number }}</td>
                                        <td class="small fw-semibold">{{ $sale->customer->full_name }}</td>
                                        <td class="small fw-bold text-danger">{{ number_format(abs($sale->total_amount), 2) }}
                                        </td>
                                        <td class="small fw-bold text-warning">{{ number_format(abs($sale->paid_amount), 2) }}
                                        </td>
                                        <td class="small text-muted">{{ Str::limit($sale->notes, 40) }}</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('sales.show', $sale->id) }}"
                                                class="btn btn-sm btn-icon border-0 text-muted" title="View">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted small">No returns found.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($sales->hasPages())
                        <div class="p-4 border-top">
                            {{ $sales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-icon:hover {
            background: #f3f4f6;
            color: #6366f1 !important;
        }
    </style>
@endsection