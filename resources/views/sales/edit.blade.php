@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <h1 class="h3 fw-bold text-gray-800">Edit Sale Invoice #{{ $sale->invoice_number }}</h1>
    </div>

    <form action="{{ route('sales.update', $sale->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Customer</label>
                                <select class="form-select" name="customer_id" required>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Date</label>
                                <input type="date" class="form-control" name="sale_date" value="{{ $sale->sale_date }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Salesman</label>
                                <select class="form-select select2" name="salesman_id">
                                    <option value="">Select Salesman</option>
                                    @foreach($salesmen as $man)
                                        <option value="{{ $man->id }}" {{ $sale->salesman_id == $man->id ? 'selected' : '' }}>{{ $man->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning small">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Editing items is currently restricted to maintain stock integrity. To change items, please delete and recreate the invoice or use return notes.
                        </div>

                        <!-- Read-only items list -->
                         <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle bg-light">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product->name }}
                                            @if($item->description)
                                                <br><small class="text-muted fst-italic">({{ $item->description }})</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea class="form-control" name="notes" rows="3">{{ $sale->notes }}</textarea>
                        </div>
                        
                         <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4" style="background: #6366f1; border: none;">Update Invoice</button>
                            <a href="{{ route('sales.index') }}" class="btn btn-light px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
