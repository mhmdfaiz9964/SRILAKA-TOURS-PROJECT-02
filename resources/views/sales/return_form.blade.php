@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="content-header mb-4">
            <h1 class="h3 fw-bold text-gray-800">Sales Return</h1>
            <p class="text-muted">Returning items for Invoice #{{ $sale->invoice_number }}</p>
        </div>

        <form action="{{ route('sales.return.store') }}" method="POST" id="returnForm">
            @csrf
            <input type="hidden" name="original_sale_id" value="{{ $sale->id }}">

            <div class="row">
                <!-- Left Side: Return Details -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Customer</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $sale->customer->full_name }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Return Number</label>
                                    <input type="text" class="form-control bg-light" name="return_number"
                                        value="{{ $returnNumber }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Return Date</label>
                                    <input type="date" class="form-control" name="return_date" value="{{ date('Y-m-d') }}"
                                        required>
                                </div>
                            </div>

                            <!-- Product Items Table -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 40%;">Product</th>
                                            <th style="width: 15%;">Unit Price</th>
                                            <th style="width: 15%;">Original Qty</th>
                                            <th style="width: 15%;">Return Qty</th>
                                            <th style="width: 15%;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $item->product->name }}</div>
                                                    <div class="small text-muted">{{ $item->product->code }}</div>
                                                    <input type="hidden" name="items[{{ $item->id }}][product_id]"
                                                        value="{{ $item->product_id }}">
                                                    <input type="hidden" name="items[{{ $item->id }}][unit_price]"
                                                        value="{{ $item->unit_price }}">
                                                </td>
                                                <td>LKR {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-muted">{{ $item->quantity }}</td>
                                                <td>
                                                    <input type="number" step="0.01"
                                                        class="form-control form-control-sm return-qty"
                                                        name="items[{{ $item->id }}][quantity]"
                                                        data-price="{{ $item->unit_price }}" data-max="{{ $item->quantity }}"
                                                        value="0" min="0" max="{{ $item->quantity }}"
                                                        oninput="calculateRowTotal(this)">
                                                </td>
                                                <td class="text-end fw-bold row-total">0.00</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end">
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted fw-bold">Return Subtotal:</span>
                                        <span class="fw-bold" id="subTotal">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                        <span class="fw-bold h5 mb-0">Total Return:</span>
                                        <span class="fw-bold h5 mb-0 text-danger" id="totalReturn">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Money Return & Notes -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3">Finance Adjustment</h6>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Cash Return Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">LKR</span>
                                    <input type="number" step="0.01" class="form-control" name="cash_return_amount"
                                        id="cashReturnAmount" value="0" oninput="calculateNetAdjustment()">
                                </div>
                                <div class="form-text small text-muted">Amount actually paid back to customer in cash.</div>
                            </div>

                            <div class="alert alert-info py-2 rounded-3 border-0 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Return:</span>
                                    <strong id="infoTotalReturn">0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cash Back:</span>
                                    <strong id="infoCashBack">0.00</strong>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between">
                                    <span>A/C Reduction:</span>
                                    <strong id="infoAcReduction">0.00</strong>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"
                                    placeholder="Reason for return..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning py-2 fw-bold" id="submitBtn">
                                    <i class="fa-solid fa-check me-2"></i> Confirm Return
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function calculateRowTotal(input) {
            const qty = parseFloat(input.value) || 0;
            const max = parseFloat(input.getAttribute('data-max')) || 0;
            const price = parseFloat(input.getAttribute('data-price')) || 0;

            if (qty > max) {
                input.value = max;
                return calculateRowTotal(input);
            }

            const total = qty * price;
            input.closest('tr').querySelector('.row-total').innerText = total.toLocaleString(undefined, { minimumFractionDigits: 2 });

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let subtotal = 0;
            document.querySelectorAll('.return-qty').forEach(input => {
                const qty = parseFloat(input.value) || 0;
                const price = parseFloat(input.getAttribute('data-price')) || 0;
                subtotal += qty * price;
            });

            document.getElementById('subTotal').innerText = subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
            document.getElementById('totalReturn').innerText = subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 });

            calculateNetAdjustment();
        }

        function calculateNetAdjustment() {
            const totalReturn = parseFloat(document.getElementById('totalReturn').innerText.replace(/,/g, '')) || 0;
            const cashBack = parseFloat(document.getElementById('cashReturnAmount').value) || 0;

            document.getElementById('infoTotalReturn').innerText = totalReturn.toLocaleString(undefined, { minimumFractionDigits: 2 });
            document.getElementById('infoCashBack').innerText = cashBack.toLocaleString(undefined, { minimumFractionDigits: 2 });

            const acReduction = totalReturn - cashBack;
            document.getElementById('infoAcReduction').innerText = acReduction.toLocaleString(undefined, { minimumFractionDigits: 2 });
        }
    </script>
@endsection