@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Create Sales Return</h1>
        <p class="text-muted small">Select an invoice to process a return</p>
    </div>

    <form action="{{ route('sales.return.store') }}" method="POST" id="returnForm">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 border-bottom">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-primary">SELECT INVOICE <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="original_sale_id" id="original_sale_id" required onchange="loadSaleDetails(this.value)">
                                    <option value="">Search Invoice Number or Customer...</option>
                                    @foreach($sales as $sale)
                                        <option value="{{ $sale->id }}">{{ $sale->invoice_number }} - {{ $sale->customer->full_name }} ({{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Customer</label>
                                <input type="text" class="form-control bg-light" id="customer_name" readonly>
                                <input type="hidden" name="customer_id" id="customer_id">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Return Number</label>
                                <input type="text" class="form-control fw-bold text-danger" name="return_number" value="{{ $returnNumber }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Return Date</label>
                                <input type="date" class="form-control" name="return_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Product Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40%;">Product</th>
                                        <th style="width: 15%;">Unit Price</th>
                                        <th style="width: 15%;">Sold Qty</th>
                                        <th style="width: 15%;">Return Qty</th>
                                        <th style="width: 15%;">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="productRows">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">Please select an invoice first</td>
                                    </tr>
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
                                    <span class="fw-bold h5 mb-0">Total Return Value:</span>
                                    <span class="fw-bold h5 mb-0 text-danger" id="grandTotal">0.00</span>
                                    <input type="hidden" name="total_amount" id="hiddenTotalAmount">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-muted small mb-3">Return Action</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">Cash Refund to Customer</label>
                            <div class="input-group">
                                <span class="input-group-text">LKR</span>
                                <input type="number" step="0.01" class="form-control fw-bold" name="cash_return_amount" id="cash_return_amount" value="0">
                            </div>
                            <p class="text-muted small mt-1">Leave 0 if this return only reduces customer's outstanding balance.</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Reason for return..."></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger py-2 fw-bold" id="submitBtn">
                                <i class="fa-solid fa-check me-2"></i> Process Return
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function loadSaleDetails(saleId) {
        if (!saleId) return;

        // Show loading state
        document.getElementById('productRows').innerHTML = '<tr><td colspan="5" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>';

        fetch(`/sales/${saleId}/fetch-data`)
            .then(res => res.json())
            .then(sale => {
                document.getElementById('customer_name').value = sale.customer.full_name;
                document.getElementById('customer_id').value = sale.customer_id;
                
                let html = '';
                sale.items.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>
                                <div class="fw-bold small">${item.product.name}</div>
                                <div class="text-muted ultra-small">${item.product.code || ''}</div>
                                <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                <input type="hidden" name="items[${index}][original_item_id]" value="${item.id}">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm bg-light" name="items[${index}][unit_price]" value="${item.unit_price}" readonly id="price_${index}">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm bg-light" value="${item.quantity}" readonly>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm border-primary" 
                                    name="items[${index}][quantity]" id="qty_${index}" value="0" min="0" max="${item.quantity}" 
                                    oninput="calcRowTotal(${index}, ${item.quantity})">
                            </td>
                            <td class="text-end fw-bold" id="total_${index}">0.00</td>
                        </tr>
                    `;
                });
                document.getElementById('productRows').innerHTML = html;
                calculateGrandTotal();
            })
            .catch(err => {
                console.error(err);
                alert('Error loading sale details.');
            });
    }

    function calcRowTotal(index, maxQty) {
        const qtyInput = document.getElementById(`qty_${index}`);
        let qty = parseFloat(qtyInput.value) || 0;
        
        if (qty > maxQty) {
            qty = maxQty;
            qtyInput.value = maxQty;
            Swal.fire('Warning', 'Return quantity cannot exceed sold quantity.', 'warning');
        }

        const price = parseFloat(document.getElementById(`price_${index}`).value) || 0;
        const total = qty * price;
        
        document.getElementById(`total_${index}`).innerText = total.toFixed(2);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let subtotal = 0;
        document.querySelectorAll('[id^="total_"]').forEach(el => {
            subtotal += parseFloat(el.innerText) || 0;
        });
        
        document.getElementById('subTotal').innerText = subtotal.toFixed(2);
        document.getElementById('grandTotal').innerText = subtotal.toFixed(2);
        document.getElementById('hiddenTotalAmount').value = subtotal.toFixed(2);
    }
</script>

<style>
    .ultra-small { font-size: 0.7rem; }
</style>
@endsection
