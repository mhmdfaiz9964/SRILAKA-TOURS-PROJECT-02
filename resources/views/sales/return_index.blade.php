@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-0 fw-bold text-danger">Sales Returns</h4>
                    <p class="text-muted small mb-0">History of all product returns and stock restorations</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('sales.return.create_standalone') }}"
                        class="btn btn-warning btn-sm px-3 shadow-sm d-flex align-items-center gap-2 text-white"
                        style="background: #f59e0b; border: none;">
                        <i class="fa-solid fa-plus"></i> Create Sales Return
                    </a>
                    <a href="{{ route('sales.index') }}"
                        class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2"
                        style="background: #6366f1; border: none;">
                        <i class="fa-solid fa-cart-shopping"></i> View Sales
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #fdfdfd; border-bottom: 1px solid #f3f4f6;">
                                <tr>
                                    <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase">Date</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Return #</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Original INV</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Customer</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Total Returned</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">Cash Backed</th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr class="cursor-pointer" onclick="window.location='{{ route('sales.show', $sale->id) }}'">
                                        <td class="ps-4 text-muted small">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}
                                        </td>
                                        <td class="fw-bold text-dark small">{{ $sale->invoice_number }}</td>
                                        <td class="small fw-semibold text-primary">
                                            {{ $sale->originalSale ? $sale->originalSale->invoice_number : '-' }}
                                        </td>
                                        <td class="small fw-semibold">{{ $sale->customer->full_name }}</td>
                                        <td class="small fw-bold text-danger">LKR {{ number_format(abs($sale->total_amount), 2) }}</td>
                                        <td class="small fw-bold text-warning">LKR {{ number_format(abs($sale->paid_amount), 2) }}</td>
                                        <td class="text-end pe-4" onclick="event.stopPropagation();">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <a href="{{ route('sales.show', $sale->id) }}"
                                                    class="btn btn-sm btn-icon border-0 text-muted" title="View Details">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
                                                 @can('sale-delete')
                                                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST"
                                                        class="d-inline delete-form m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon border-0 text-danger delete-btn"
                                                            title="Delete">
                                                            <i class="fa-regular fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted small">No return records found.</div>
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
        .cursor-pointer { cursor: pointer; }
        .btn-icon:hover {
            background: #f3f4f6;
            color: #6366f1 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Delete Return?',
                        text: "This will remove the return record. Note: It may not automatically reverse stock/ledgers.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#d1d5db',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection