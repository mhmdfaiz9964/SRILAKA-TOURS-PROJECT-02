@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">New Purchase Record</h1>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf
        <div class="row">
            <!-- Left Side: Purchase Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Type</label>
                                <select class="form-select" name="purchase_type">
                                    <option value="local">Local</option>
                                    <option value="import">Import</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small">Supplier <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select select2" name="supplier_id" id="supplier_id" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->full_name }} - {{ $supplier->company_name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createSupplierModal"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Supplier Invoice #</label>
                                <input type="text" class="form-control" name="invoice_number" placeholder="Optional">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">GRN Number</label>
                                <input type="text" class="form-control" name="grn_number" placeholder="GRN...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Date</label>
                                <input type="date" class="form-control" name="purchase_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Product Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 30%;">Product</th>
                                        <th style="width: 20%;">Description</th>
                                        <th style="width: 15%;">Cost Price</th>
                                        <th style="width: 10%;">Qty</th>
                                        <th style="width: 20%;">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="productRows">
                                    <!-- Rows via JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="p-2">
                                            <button type="button" class="btn btn-light btn-sm w-100 fw-bold border-dashed text-primary" onclick="addProductRow()">
                                                <i class="fa-solid fa-plus me-1"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Additional Costs -->
                        <div class="row g-3 mb-4 bg-light p-3 rounded-3 mx-0">
                            <div class="col-12"><h6 class="fw-bold small text-uppercase text-muted">Additional Costs</h6></div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Broker</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="broker_cost" id="broker_cost" value="0" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Transport</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="transport_cost" id="transport_cost" value="0" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Loading</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="loading_cost" id="loading_cost" value="0" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Unloading</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="unloading_cost" id="unloading_cost" value="0" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Labour Charges</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="labour_cost" id="labour_cost" value="0" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Air Ticket</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="air_ticket_cost" id="air_ticket_cost" value="0" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Other Expenses</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="other_expenses" id="other_expenses" value="0" oninput="calculateTotal()">
                            </div>
                        </div>

                         <!-- Investors Section -->
                         <div class="mb-4">
                            <h6 class="fw-bold small text-uppercase text-muted mb-2">Investors</h6>
                            <table class="table table-bordered table-sm" id="investorTable">
                                <thead>
                                    <tr>
                                        <th>Investor Name</th>
                                        <th width="30%">Amount</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="investorRows">
                                    <!-- Dynamic Rows -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3">
                                            <button type="button" class="btn btn-light btn-sm w-100 fw-bold border-dashed text-primary" onclick="addInvestorRow()">
                                                <i class="fa-solid fa-plus me-1"></i> Add Investor
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fw-bold h5 mb-0">Total Amount:</span>
                                    <span class="fw-bold h5 mb-0 text-primary" id="totalAmountDisplay">0.00</span>
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
                            <label class="form-label fw-bold small">Payment Method</label>
                            <select class="form-select" name="payment_method" id="paymentMethod" onchange="togglePaymentFields()">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit">Credit / Unpaid</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Amount Paid</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" name="paid_amount" id="paidAmount" value="0">
                            </div>
                        </div>

                        <!-- Cheque Details -->
                        <div id="chequeFields" class="d-none border rounded p-3 bg-light mb-3">
                            <h6 class="small fw-bold mb-2">Cheque Information</h6>
                            <div class="mb-2">
                                <label class="small text-muted">Cheque Number</label>
                                <input type="text" class="form-control form-control-sm" name="cheque_number">
                            </div>
                            <div class="mb-2">
                                <label class="small text-muted">Bank</label>
                                <select class="form-select form-select-sm" name="bank_id">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small text-muted">Cheque Date</label>
                                <input type="date" class="form-control form-control-sm" name="cheque_date">
                            </div>
                            <div class="mb-0">
                                <label class="small text-muted">Payee Name (Us)</label>
                                <input type="text" class="form-control form-control-sm" name="payee_name" value="{{ config('app.name') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold" style="background: #6366f1; border: none;">
                                <i class="fa-solid fa-check me-2"></i> Save Purchase
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>



<script>
    // Embed products for price lookup if exists
    const existingProducts = {!! json_encode($products) !!};

    function addProductRow() {
        const rowId = Date.now();
        const html = `
            <tr id="row_${rowId}">
                <td>
                    <select class="form-select form-select-sm product-select" name="items[${rowId}][existing_product_id]" onchange="updateProductDetails(this, ${rowId})">
                        <option value="">Select Product...</option>
                        ${existingProducts.map(p => `<option value="${p.id}" data-cost="${p.cost_price}">${p.name} - ${p.code ?? ''}</option>`).join('')}
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
        
        if(cost > 0) {
            document.getElementById(`price_${rowId}`).value = cost;
            calcRowTotal(rowId);
        }
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
        let total = 0;
        document.querySelectorAll('[id^="hiddenTotal_"]').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        
        document.getElementById('totalAmountDisplay').innerText = total.toFixed(2);
        document.getElementById('hiddenTotalAmount').value = total.toFixed(2);
        
        if(document.getElementById('paymentMethod').value !== 'credit') {
            document.getElementById('paidAmount').value = total.toFixed(2);
        }
    }

    function togglePaymentFields() {
        const method = document.getElementById('paymentMethod').value;
        const cheque = document.getElementById('chequeFields');
        
        if(method === 'cheque') {
            cheque.classList.remove('d-none');
        } else {
            cheque.classList.add('d-none');
        }
        
        if(method === 'credit') {
            document.getElementById('paidAmount').value = 0;
        } else {
            calculateTotal();
        }
    }

    function addInvestorRow() {
        const rowId = Date.now() + Math.random();
        const html = `
            <tr id="inv_row_${rowId}">
                <td><input type="text" class="form-control form-control-sm" name="investors[${rowId}][name]" placeholder="Investor Name"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="investors[${rowId}][amount]" placeholder="0.00"></td>
                <td class="text-center">
                     <button type="button" class="btn btn-link text-danger p-0" onclick="document.getElementById('inv_row_${rowId}').remove()"><i class="fa-solid fa-xmark"></i></button>
                </td>
            </tr>
        `;
        document.getElementById('investorRows').insertAdjacentHTML('beforeend', html);
    }

    // Initialize with one row
    addProductRow();

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('[id^="hiddenTotal_"]').forEach(el => {
            total += parseFloat(el.value) || 0;
        });

        const broker = parseFloat(document.getElementById('broker_cost').value) || 0;
        const transport = parseFloat(document.getElementById('transport_cost').value) || 0;
        const loading = parseFloat(document.getElementById('loading_cost').value) || 0;
        const unloading = parseFloat(document.getElementById('unloading_cost').value) || 0;
        const labour = parseFloat(document.getElementById('labour_cost').value) || 0;
        const air = parseFloat(document.getElementById('air_ticket_cost').value) || 0;
        const other = parseFloat(document.getElementById('other_expenses').value) || 0;
        
        const finalTotal = total + broker + transport + loading + unloading + labour + air + other;
        
        document.getElementById('totalAmountDisplay').innerText = finalTotal.toFixed(2);
        document.getElementById('hiddenTotalAmount').value = finalTotal.toFixed(2);
        
        if(document.getElementById('paymentMethod').value !== 'credit') {
            document.getElementById('paidAmount').value = finalTotal.toFixed(2);
        }
    }
</script>

<!-- Create Supplier Modal -->
<div class="modal fade" id="createSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0">
                <h6 class="modal-title fw-bold">New Supplier</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <form id="createSupplierForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Company Name</label>
                        <input type="text" class="form-control" name="company_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contact Person</label>
                         <div class="row g-2">
                             <div class="col-6"><input type="text" class="form-control" name="first_name" placeholder="First Name" required></div>
                             <div class="col-6"><input type="text" class="form-control" name="last_name" placeholder="Last Name" required></div>
                         </div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold">Mobile</label>
                        <input type="text" class="form-control" name="mobile_number">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill btn-sm">Save Supplier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add Supplier Modal Script
        document.getElementById('createSupplierForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Combine names for backend
            const fullName = `${formData.get('first_name')} ${formData.get('last_name')}`;
            formData.append('full_name', fullName);
            formData.append('contact_number', formData.get('mobile_number'));
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("suppliers.store") }}', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const select = document.getElementById('supplier_id');
                    const text = `${data.supplier.company_name} - ${data.supplier.full_name}`;
                    const val = data.supplier.id;
                    
                    // Add to select2 if initialized
                    if ($(select).hasClass("select2-hidden-accessible")) {
                        var newOption = new Option(text, val, true, true);
                        $(select).append(newOption).trigger('change');
                    } else {
                        const option = new Option(text, val);
                        select.add(option, undefined);
                        select.value = val;
                    }
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createSupplierModal'));
                    modal.hide();
                    this.reset();
                } else {
                    alert('Error creating supplier: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error creating supplier. Check console.');
            });
        });
    });
</script>
@endsection
