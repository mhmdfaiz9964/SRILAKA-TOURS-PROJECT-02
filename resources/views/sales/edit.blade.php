@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Edit Sale Invoice #{{ $sale->invoice_number }}</h1>
    </div>

    <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="saleForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Left Side: Invoice Details -->
             @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Customer <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select select2" name="customer_id" id="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->full_name }} ({{ $customer->mobile_number }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Invoice Number</label>
                                <input type="text" class="form-control" name="invoice_number" value="{{ $sale->invoice_number }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Salesman</label>
                                <input type="text" class="form-control" name="salesman_name" value="{{ $sale->salesman_name }}" placeholder="Name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Date</label>
                                <input type="date" class="form-control" name="sale_date" value="{{ $sale->sale_date }}" required>
                            </div>
                        </div>

                        <!-- Product Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 25%;">Product</th>
                                        <th style="width: 25%;">Type / Description</th>
                                        <th style="width: 10%;">Unit</th>
                                        <th style="width: 12%;">Price</th>
                                        <th style="width: 13%;">Qty</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="productRows">
                                    <!-- Rows added via JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="p-2">
                                            <button type="button" class="btn btn-light btn-sm w-100 fw-bold border-dashed text-primary" onclick="addProductRow()">
                                                <i class="fa-solid fa-plus me-1"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted fw-bold">Subtotal:</span>
                                    <span class="fw-bold" id="subTotal">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                    <span class="text-muted fw-bold">Transport:</span>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end w-50" name="transport_cost" id="transport_cost" value="{{ $sale->transport_cost }}" oninput="calculateGrandTotal()">
                                </div>
                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                    <span class="text-muted fw-bold">Round Discount:</span>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end w-50" name="discount_amount" id="discount_amount" value="{{ $sale->discount_amount }}" oninput="calculateGrandTotal()">
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fw-bold h5 mb-0">Grand Total:</span>
                                    <span class="fw-bold h5 mb-0 text-primary" id="grandTotal">0.00</span>
                                    <input type="hidden" name="total_amount" id="hiddenTotalAmount">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Payment & Notes -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-muted small mb-3">Payment Details</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Payment Status</label>
                            <div class="d-flex gap-2">
                                <div class="badge bg-{{ $sale->status == 'paid' ? 'success' : ($sale->status == 'partial' ? 'warning' : 'danger') }}-subtle text-{{ $sale->status == 'paid' ? 'success' : ($sale->status == 'partial' ? 'warning' : 'danger') }} border px-3 py-2 rounded-pill">
                                    Current Status: {{ ucfirst($sale->status) }}
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Payment editing on this page is restricted to avoid accounting conflicts. 
                                Please use the "Add Payment" feature on the View page for additional payments.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Total Paid So Far</label>
                            <input type="text" class="form-control bg-light" value="{{ number_format($sale->paid_amount, 2) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea class="form-control" name="notes" rows="3">{{ $sale->notes }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold" style="background: #6366f1; border: none;">
                                <i class="fa-solid fa-check me-2"></i> Update Sale
                            </button>
                            <a href="{{ route('sales.index') }}" class="btn btn-light py-2 fw-bold">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const products = {!! json_encode($products) !!};
    // Pre-load existing items
    const existingItems = {!! json_encode($sale->items) !!};

    document.addEventListener('DOMContentLoaded', function() {
        existingItems.forEach(item => {
            addProductRow(item);
        });
        calculateGrandTotal();
    });

    function addProductRow(data = null) {
        const rowId = Date.now() + Math.floor(Math.random() * 1000);
        const productId = data ? data.product_id : '';
        const desc = data ? (data.description || '') : '';
        const price = data ? data.unit_price : '';
        const qty = data ? data.quantity : 1;
        const total = data ? data.total_price : 0;
        
        // Find product unit if existing
        let unit = '';
        if(data && data.product) unit = data.product.units || '';

        const html = `
            <tr id="row_${rowId}">
                <td>
                    <select class="form-select form-select-sm product-select" name="items[${rowId}][product_id]" onchange="updateProductDetails(${rowId}, this)">
                        <option value="">Select Product...</option>
                        ${products.map(p => `<option value="${p.id}" ${p.id == productId ? 'selected' : ''} data-price="${p.sale_price}" data-unit="${p.units || ''}">${p.name} - ${p.code}</option>`).join('')}
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" name="items[${rowId}][description]" value="${desc}" placeholder="Desc..."></td>
                <td><input type="text" class="form-control form-control-sm bg-light" id="unit_${rowId}" value="${unit}" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[${rowId}][unit_price]" id="price_${rowId}" value="${price}" oninput="calcRowTotal(${rowId})"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[${rowId}][quantity]" id="qty_${rowId}" value="${qty}" oninput="calcRowTotal(${rowId})"></td>
                <td class="text-end fw-bold" id="total_${rowId}">${data ? parseFloat(total).toFixed(2) : '0.00'}</td>
                <input type="hidden" name="items[${rowId}][total_price]" id="hiddenTotal_${rowId}" value="${data ? parseFloat(total).toFixed(2) : '0.00'}">
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(${rowId})"><i class="fa-solid fa-xmark"></i></button>
                </td>
            </tr>
        `;
        document.getElementById('productRows').insertAdjacentHTML('beforeend', html);
        
        // If it was a new row (click), update unit/price defaults? No, let user select.
        // If existing, we set values in HTML.
    }

    function updateProductDetails(rowId, select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price') || 0;
        const unit = option.getAttribute('data-unit') || '';
        
        // Only auto-fill price if it's empty or user just selected a new product 
        // (For edit mode, we preserve existing price initially, but if changed, update it)
        document.getElementById(`price_${rowId}`).value = price;
        document.getElementById(`unit_${rowId}`).value = unit;
        calcRowTotal(rowId);
    }

    function calcRowTotal(rowId) {
        const price = parseFloat(document.getElementById(`price_${rowId}`).value) || 0;
        const qty = parseFloat(document.getElementById(`qty_${rowId}`).value) || 0;
        
        let total = price * qty;
        
        document.getElementById(`total_${rowId}`).innerText = total.toFixed(2);
        document.getElementById(`hiddenTotal_${rowId}`).value = total.toFixed(2);
        calculateGrandTotal();
    }

    function removeRow(rowId) {
        document.getElementById(`row_${rowId}`).remove();
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let subtotal = 0;
        document.querySelectorAll('[id^="hiddenTotal_"]').forEach(el => {
            subtotal += parseFloat(el.value) || 0;
        });
        
        const roundDisc = parseFloat(document.getElementById('discount_amount').value) || 0;
        const transport = parseFloat(document.getElementById('transport_cost').value) || 0;
        
        const grandTotal = subtotal + transport - roundDisc;
        
        document.getElementById('subTotal').innerText = subtotal.toFixed(2);
        document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
        document.getElementById('hiddenTotalAmount').value = grandTotal.toFixed(2);
    }
</script>
@endsection
