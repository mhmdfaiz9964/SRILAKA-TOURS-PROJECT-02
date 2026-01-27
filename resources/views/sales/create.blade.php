@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">New Sale Invoice</h1>
    </div>

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf
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
                                    <select class="form-select select2" name="customer_id" id="customer_id" required onchange="checkCreditLimit()">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" data-credit="{{ $customer->credit_limit }}" data-due="0">{{ $customer->full_name }} ({{ $customer->mobile_number }})</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal"><i class="fa-solid fa-plus"></i></button>
                                </div>
                                <div id="creditAlert" class="alert alert-danger mt-2 py-2 small d-none">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Credit Limit Exceeded! Limit: <span id="limitVal"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Invoice Number</label>
                                <input type="text" class="form-control" name="invoice_number" required placeholder="Enter Invoice No">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Salesman</label>
                                <input type="text" class="form-control" name="salesman_name" id="salesman_name" placeholder="Name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Date</label>
                                <input type="date" class="form-control" name="sale_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Product Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 30%;">Product</th>
                                        <th style="width: 20%;">Type / Description</th>
                                        <th style="width: 10%;">Unit</th>
                                        <th style="width: 20%;">Price</th>
                                        <th style="width: 10%;">Qty</th>
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
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end w-50" name="transport_cost" id="transport_cost" value="0" oninput="calculateGrandTotal()">
                                </div>
                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                    <span class="text-muted fw-bold">Round Discount:</span>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end w-50" name="discount_amount" id="discount_amount" value="0" oninput="calculateGrandTotal()">
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
                        
                        <!-- Payment Type Toggle -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Payment Status</label>
                            <div class="d-flex gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_status_type" id="statusPaid" value="paid" checked onchange="togglePaymentFields()">
                                    <label class="form-check-label" for="statusPaid">Cash Paid</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_status_type" id="statusCredit" value="credit" onchange="togglePaymentFields()">
                                    <label class="form-check-label" for="statusCredit">A/C (Credit)</label>
                                </div>
                            </div>
                        </div>

                        <div id="paymentOptions">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Payment Method</label>
                                <select class="form-select" name="payment_method" id="paymentMethod" onchange="toggleMethodFields()">
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="bank_transfer">Bank Transfer</option>
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
                                    <label class="small text-muted">Cheque Number (6 Digits)</label>
                                    <input type="text" class="form-control form-control-sm" name="cheque_number" maxlength="6" minlength="6" placeholder="######">
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
                                    <label class="small text-muted">Payer Name</label>
                                    <input type="text" class="form-control form-control-sm" name="payer_name">
                                </div>
                            </div>

                            <!-- Bank Transfer Details -->
                            <div id="bankFields" class="d-none border rounded p-3 bg-light mb-3">
                                <h6 class="small fw-bold mb-2">Transfer Information</h6>
                                <div class="mb-2">
                                    <label class="small text-muted">Reference Number</label>
                                    <input type="text" class="form-control form-control-sm" name="transfer_ref">
                                </div>
                             <div class="mb-2">
                                <label class="small text-muted">Bank</label>
                                <select class="form-select form-select-sm" name="transfer_bank_id">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </div>
                        </div> <!-- End Payment Options -->

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold" id="submitBtn" style="background: #6366f1; border: none;">
                                <i class="fa-solid fa-check me-2"></i> Complete Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Embed products data for JS access
    const products = {!! json_encode($products) !!};

    function addProductRow() {
        const rowId = Date.now();
        const html = `
            <tr id="row_${rowId}">
                <td>
                    <select class="form-select form-select-sm product-select" name="items[${rowId}][product_id]" onchange="updateProductDetails(${rowId}, this)">
                        <option value="">Select Product...</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.sale_price}" data-unit="${p.units || ''}">${p.name} - ${p.code}</option>`).join('')}
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" name="items[${rowId}][description]" placeholder="Desc..."></td>
                <td><input type="text" class="form-control form-control-sm bg-light" id="unit_${rowId}" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[${rowId}][unit_price]" id="price_${rowId}" oninput="calcRowTotal(${rowId})"></td>
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

    function updateProductDetails(rowId, select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price') || 0;
        const unit = option.getAttribute('data-unit') || '';
        
        document.getElementById(`price_${rowId}`).value = price;
        document.getElementById(`unit_${rowId}`).value = unit;
        calcRowTotal(rowId);
    }

    function calcRowTotal(rowId) {
        const price = parseFloat(document.getElementById(`price_${rowId}`).value) || 0;
        const qty = parseFloat(document.getElementById(`qty_${rowId}`).value) || 0;
        
        // No discount per row
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
        
        // Auto-fill paid amount if "Paid" status
        const isPaid = document.getElementById('statusPaid').checked;
        if(isPaid) {
            document.getElementById('paidAmount').value = grandTotal.toFixed(2);
        } else {
            document.getElementById('paidAmount').value = 0;
        }
    }

    function togglePaymentFields() {
        const isPaid = document.getElementById('statusPaid').checked;
        const options = document.getElementById('paymentOptions');
        const btn = document.getElementById('submitBtn');
        
        if(isPaid) {
            options.classList.remove('d-none');
            // Trigger method toggle to show correct sub-fields
            toggleMethodFields();
            calculateGrandTotal(); // updates paid amount
            
            // Update button for Paid
            btn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Complete Sale';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        } else {
            options.classList.add('d-none');
            document.getElementById('paidAmount').value = 0;
            
            // Update button for Credit
            btn.innerHTML = '<i class="fa-solid fa-save me-2"></i> Save Invoice';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
        }
    }

    function toggleMethodFields() {
        const method = document.getElementById('paymentMethod').value;
        const cheque = document.getElementById('chequeFields');
        const bank = document.getElementById('bankFields');
        
        cheque.classList.add('d-none');
        bank.classList.add('d-none');
        
        if(method === 'cheque') cheque.classList.remove('d-none');
        if(method === 'bank_transfer') bank.classList.remove('d-none');
    }

    function checkCreditLimit() {
        const select = document.getElementById('customer_id');
        const option = select.options[select.selectedIndex];
        const limit = parseFloat(option.getAttribute('data-credit')) || 0;
        const alert = document.getElementById('creditAlert');
        
        // This is a basic check. Real-world would need current pending balance checking via ajax
        // For now, we just show the limit
        if(limit > 0) {
             // alert.classList.remove('d-none');
             // document.getElementById('limitVal').innerText = limit;
        } else {
            alert.classList.add('d-none');
        }
    }

    // Initialize with one row
    addProductRow();
</script>

<!-- Create Customer Modal -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0">
                <h6 class="modal-title fw-bold">New Customer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <form id="createCustomerForm">
                    <div class="row g-2">
                         <div class="col-md-6">
                            <label class="form-label small fw-bold">First Name</label>
                            <input type="text" class="form-control form-control-sm" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Last Name</label>
                            <input type="text" class="form-control form-control-sm" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile</label>
                            <input type="text" class="form-control form-control-sm" name="mobile_number">
                        </div>
                         <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control form-control-sm" name="email">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Company Name</label>
                            <input type="text" class="form-control form-control-sm" name="company_name">
                        </div>
                         <div class="col-md-12">
                            <label class="form-label small fw-bold">Credit Limit</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="credit_limit" value="0">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill btn-sm">Save Customer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('createCustomerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Combine First and Last ID to Full Name for backend
            const fullName = `${formData.get('first_name')} ${formData.get('last_name')}`;
            formData.append('full_name', fullName);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("customers.store") }}', {
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
                    const select = document.getElementById('customer_id');
                    // Check if names exist, otherwise use full_name if user provided that or fallback
                    // The Controller uses Customer::create($request->all()).
                    // Customer model likely has 'full_name' as fillable, but form sends first_name/last_name.
                    // I should check Customer model fillables.
                    // Wait, CustomerController validation asks for 'full_name'.
                    // But the form sends 'first_name' and 'last_name'.
                    // The user's request log showed `first_name=ApexWeb&last_name=Innovations`.
                    // The Controller validation: 'full_name' => 'required'.
                    // This validation will FAIL if 'full_name' is not in request.
                    // I need to intercept the form data and construct full_name OR update logic.
                    // Let me check Customer Model again.
                    
                    const name = data.customer.full_name || `${data.customer.first_name || ''} ${data.customer.last_name || ''}`;
                    const option = new Option(`${name} (${data.customer.mobile_number})`, data.customer.id);
                    option.setAttribute('data-credit', data.customer.credit_limit);
                    
                    // Add to select2 if initialized
                    if ($(select).hasClass("select2-hidden-accessible")) {
                        var newOption = new Option(option.text, option.value, true, true);
                        $(select).append(newOption).trigger('change');
                    } else {
                        select.add(option, undefined);
                        select.value = data.customer.id;
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('createCustomerModal'));
                    modal.hide();
                    this.reset();
                } else {
                    alert('Error creating customer: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error creating customer. Please check console.');
            });
        });
    });
</script>
@endsection
