@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Edit Purchase #{{ $purchase->invoice_number }}</h1>
    </div>

    <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Supplier</label>
                                <select class="form-select" name="supplier_id" required>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Invoice #</label>
                                <input type="text" class="form-control" name="invoice_number" value="{{ $purchase->invoice_number }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">GRN Number</label>
                                <input type="text" class="form-control" name="grn_number" value="{{ $purchase->grn_number }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Date</label>
                                <input type="date" class="form-control" name="purchase_date" value="{{ $purchase->purchase_date }}" required>
                            </div>
                        </div>
                        
                        <!-- Editable items list -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 35%;">Product</th>
                                        <th style="width: 25%;">Description</th>
                                        <th style="width: 15%;">Cost</th>
                                        <th style="width: 10%;">Qty</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="productRows">
                                    @foreach($purchase->items as $item)
                                    @php $rowId = $item->id; @endphp
                                    <tr id="row_{{ $rowId }}">
                                        <td>
                                            <select class="form-select form-select-sm" name="items[{{ $rowId }}][existing_product_id]" onchange="updateProductDetails(this, {{ $rowId }})">
                                                <option value="">Select Product...</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm" name="items[{{ $rowId }}][description]" value="{{ $item->description }}" placeholder="Desc..."></td>
                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[{{ $rowId }}][cost_price]" id="price_{{ $rowId }}" value="{{ $item->cost_price }}" oninput="calcRowTotal({{ $rowId }})"></td>
                                        <td><input type="number" step="1" class="form-control form-control-sm" name="items[{{ $rowId }}][quantity]" id="qty_{{ $rowId }}" value="{{ $item->quantity }}" oninput="calcRowTotal({{ $rowId }})"></td>
                                        <td class="text-end fw-bold" id="total_{{ $rowId }}">{{ number_format($item->total_price, 2) }}</td>
                                        <input type="hidden" name="items[{{ $rowId }}][total_price]" id="hiddenTotal_{{ $rowId }}" value="{{ $item->total_price }}">
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow({{ $rowId }})"><i class="fa-solid fa-xmark"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="p-2">
                                            <button type="button" class="btn btn-light btn-sm w-100 fw-bold border-dashed text-primary" onclick="addProductRow()">
                                                <i class="fa-solid fa-plus me-1"></i> Add Another Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                         <div class="row justify-content-end mb-4">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted fw-bold">Items Total:</span>
                                    <span class="fw-bold" id="subTotalDisplay">{{ number_format($purchase->items->sum('total_price'), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fw-bold h5 mb-0">Grand Total:</span>
                                    <span class="fw-bold h5 mb-0 text-primary" id="totalAmountDisplay">{{ number_format($purchase->total_amount, 2) }}</span>
                                    <input type="hidden" name="total_amount" id="hiddenTotalAmount" value="{{ $purchase->total_amount }}">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Costs Editing -->
                        <div class="row g-3 mb-4 bg-light p-3 rounded-3 mx-0">
                            <div class="col-12"><h6 class="fw-bold small text-uppercase text-muted">Additional Costs</h6></div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Broker</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="broker_cost" id="broker_cost" value="{{ $purchase->broker_cost }}" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Transport</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="transport_cost" id="transport_cost" value="{{ $purchase->transport_cost }}" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Loading</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="loading_cost" id="loading_cost" value="{{ $purchase->loading_cost }}" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Unloading</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="unloading_cost" id="unloading_cost" value="{{ $purchase->unloading_cost }}" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Labour Charges</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="labour_cost" id="labour_cost" value="{{ $purchase->labour_cost }}" oninput="calculateTotal()">
                            </div>
                             <div class="col-md-3">
                                <label class="form-label small fw-bold">Air Ticket</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="air_ticket_cost" id="air_ticket_cost" value="{{ $purchase->air_ticket_cost }}" oninput="calculateTotal()">
                            </div>
                             <div class="col-md-3">
                                <label class="form-label small fw-bold">Other Expenses</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="other_expenses" id="other_expenses" value="{{ $purchase->other_expenses }}" oninput="calculateTotal()">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea class="form-control" name="notes" rows="3">{{ $purchase->notes }}</textarea>
                        </div>
                        
                         <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4" style="background: #6366f1; border: none;">Update Purchase</button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-light px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const existingProducts = {!! json_encode($products) !!};

    function addProductRow() {
        const rowId = Date.now();
        const html = `
            <tr id="row_${rowId}">
                <td>
                    <select class="form-select form-select-sm product-select" name="items[${rowId}][existing_product_id]" onchange="updateProductDetails(this, ${rowId})">
                        <option value="">Select Product...</option>
                        ${existingProducts.map(p => `<option value="${p.id}" data-cost="${p.cost_price}">${p.name}</option>`).join('')}
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" name="items[${rowId}][description]" placeholder="Desc..."></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[${rowId}][cost_price]" id="price_${rowId}" oninput="calcRowTotal(${rowId})"></td>
                <td><input type="number" step="1" class="form-control form-control-sm" name="items[${rowId}][quantity]" id="qty_${rowId}" value="1" oninput="calcRowTotal(${rowId})"></td>
                <td class="text-end fw-bold" id="total_${rowId}">0.00</td>
                <input type="hidden" name="items[${rowId}][total_price]" id="hiddenTotal_${rowId}">
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(${rowId})"><i class="fa-solid fa-xmark"></i></button>
                </td>
            </tr>
        `;
        document.getElementById('productRows').insertAdjacentHTML('beforeend', html);
    }

    function updateProductDetails(select, rowId) {
        const option = select.options[select.selectedIndex];
        const cost = option.getAttribute('data-cost') || 0;
        document.getElementById(`price_${rowId}`).value = cost;
        calcRowTotal(rowId);
    }

    function calcRowTotal(rowId) {
        const price = parseFloat(document.getElementById(`price_${rowId}`).value) || 0;
        const qty = parseFloat(document.getElementById(`qty_${rowId}`).value) || 0;
        const total = price * qty;
        document.getElementById(`total_${rowId}`).innerText = total.toFixed(2);
        document.getElementById(`hiddenTotal_${rowId}`).value = total.toFixed(2);
        calculateTotal();
    }

    function removeRow(rowId) {
        document.getElementById(`row_${rowId}`).remove();
        calculateTotal();
    }

    function calculateTotal() {
        let itemsTotal = 0;
        document.querySelectorAll('[id^="hiddenTotal_"]').forEach(el => {
            itemsTotal += parseFloat(el.value) || 0;
        });
        
        document.getElementById('subTotalDisplay').innerText = itemsTotal.toFixed(2);

        const broker = parseFloat(document.getElementById('broker_cost').value) || 0;
        const transport = parseFloat(document.getElementById('transport_cost').value) || 0;
        const loading = parseFloat(document.getElementById('loading_cost').value) || 0;
        const unloading = parseFloat(document.getElementById('unloading_cost').value) || 0;
        const labour = parseFloat(document.getElementById('labour_cost').value) || 0;
        const air = parseFloat(document.getElementById('air_ticket_cost').value) || 0;
        const other = parseFloat(document.getElementById('other_expenses').value) || 0;
        
        const grandTotal = itemsTotal + broker + transport + loading + unloading + labour + air + other;
        
        document.getElementById('totalAmountDisplay').innerText = grandTotal.toFixed(2);
        document.getElementById('hiddenTotalAmount').value = grandTotal.toFixed(2);
    }
</script>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
