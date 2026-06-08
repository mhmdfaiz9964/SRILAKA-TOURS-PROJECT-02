<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Payment;
use App\Models\InCheque;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('customer')->whereNull('original_sale_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('full_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest': $query->orderBy('created_at'); break;
            case 'highest_amount': $query->orderByDesc('total_amount'); break;
            case 'lowest_amount': $query->orderBy('total_amount'); break;
            default: $query->orderByDesc('created_at');
        }

        // Stats
        $statsQuery = clone $query;
        $totalSales = $statsQuery->sum('total_amount');
        $totalPaid = $statsQuery->sum('paid_amount');

        $sales = $query->paginate($request->get('per_page', 15));

        $sales->getCollection()->transform(function ($sale) {
            $sale->days_unpaid = $sale->status !== 'paid'
                ? (int) abs(now()->diffInDays($sale->created_at))
                : null;
            return $sale;
        });

        return response()->json([
            'data' => $sales->items(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
            'stats' => [
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalSales - $totalPaid,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'invoice_number' => 'required|unique:sales,invoice_number',
            'sale_date' => 'required|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'paid_amount' => 'nullable|numeric',
            'cheque_number' => 'nullable|required_if:payment_method,cheque|digits:6',
        ]);

        $sale = \DB::transaction(function () use ($request) {
            $total = $request->input('total_amount');
            $paid = $request->input('paid_amount', 0);

            $status = 'unpaid';
            if ($paid >= $total) $status = 'paid';
            elseif ($paid > 0) $status = 'partial';

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'invoice_number' => $request->invoice_number,
                'sale_date' => $request->sale_date,
                'total_amount' => $total,
                'discount_amount' => $request->discount_amount ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'paid_amount' => $paid,
                'status' => $status,
                'payment_method' => $request->payment_method,
                'salesman_name' => $request->salesman_name,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('current_stock', $item['quantity']);
                }
            }

            if ($paid > 0) {
                Payment::create([
                    'amount' => $paid,
                    'payment_date' => $sale->sale_date,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'bank_id' => $request->bank_id,
                    'payable_type' => 'App\Models\Customer',
                    'payable_id' => $sale->customer_id,
                    'type' => 'in',
                    'transaction_id' => $sale->id,
                    'transaction_type' => 'App\Models\Sale',
                    'notes' => 'Initial payment for Invoice ' . $sale->invoice_number,
                    'payment_cheque_number' => $request->cheque_number,
                    'payment_cheque_date' => $request->cheque_date,
                ]);
            }

            if ($request->payment_method === 'cheque' && $paid > 0) {
                InCheque::create([
                    'cheque_date' => $request->cheque_date,
                    'amount' => $paid,
                    'cheque_number' => $request->cheque_number,
                    'bank_id' => $request->bank_id,
                    'payer_name' => $sale->customer->full_name,
                    'status' => 'received',
                    'notes' => 'Cheque for Invoice ' . $sale->invoice_number,
                ]);
            }
            return $sale;
        });

        return response()->json([
            'message' => 'Sale created successfully',
            'sale' => $sale->load('items.product', 'customer')
        ], 201);
    }

    public function show($id)
    {
        $sale = Sale::with('items.product', 'customer', 'salesman', 'payments', 'returns.items')->findOrFail($id);
        return response()->json($sale);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required',
            'sale_date' => 'required|date',
            'items' => 'required|array',
        ]);

        \DB::transaction(function () use ($request, $id) {
            $sale = Sale::findOrFail($id);

            foreach ($sale->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) $product->increment('current_stock', $item->quantity);
            }
            $sale->items()->delete();

            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unit_price'];
                $total = $qty * $price;
                $totalAmount += $total;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $total,
                ]);

                $product = Product::find($itemData['product_id']);
                if ($product) $product->decrement('current_stock', $qty);
            }

            $transport = $request->transport_cost ?? 0;
            $discount = $request->discount_amount ?? 0;
            $grandTotal = $totalAmount + $transport - $discount;

            $status = 'unpaid';
            if ($sale->paid_amount >= $grandTotal) $status = 'paid';
            elseif ($sale->paid_amount > 0) $status = 'partial';

            $sale->update([
                'customer_id' => $request->customer_id,
                'sale_date' => $request->sale_date,
                'salesman_name' => $request->salesman_name,
                'notes' => $request->notes,
                'transport_cost' => $transport,
                'discount_amount' => $discount,
                'total_amount' => $grandTotal,
                'status' => $status,
            ]);
        });

        return response()->json(['message' => 'Sale updated successfully']);
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->items()->delete();
        $sale->delete();
        return response()->json(['message' => 'Sale deleted successfully']);
    }

    public function addPayment(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required',
            'cheque_number' => 'nullable|required_if:payment_method,cheque|digits:6',
            'cheque_date' => 'nullable|required_if:payment_method,cheque|date',
            'bank_id' => 'nullable|required_if:payment_method,cheque|exists:banks,id',
            'payer_name' => 'nullable|required_if:payment_method,cheque|string',
        ]);

        $amount = $request->amount;
        $sale->paid_amount += $amount;
        $sale->status = $sale->paid_amount >= $sale->total_amount ? 'paid' : 'partial';
        $sale->payment_method = $request->payment_method;
        $sale->save();

        $chequeId = null;
        if ($request->payment_method === 'cheque') {
            $cheque = InCheque::create([
                'cheque_date' => $request->cheque_date,
                'amount' => $amount,
                'cheque_number' => $request->cheque_number,
                'bank_id' => $request->bank_id,
                'payer_name' => $request->payer_name,
                'status' => 'received',
                'notes' => 'Payment for ' . $sale->invoice_number,
            ]);
            $chequeId = $cheque->id;
        }

        Payment::create([
            'transaction_id' => $sale->id,
            'transaction_type' => Sale::class,
            'payable_id' => $sale->customer_id,
            'payable_type' => \App\Models\Customer::class,
            'type' => 'in',
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'cheque_id' => $chequeId,
            'cheque_type' => $chequeId ? InCheque::class : null,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Payment added successfully',
            'sale' => $sale->fresh()->load('customer'),
        ]);
    }

    public function fetchSaleData($id)
    {
        $sale = Sale::with('items.product', 'customer')->findOrFail($id);
        return response()->json($sale);
    }

    public function returnIndex(Request $request)
    {
        $returns = Sale::whereNotNull('original_sale_id')
            ->with('customer', 'originalSale')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => $returns->items(),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'total' => $returns->total(),
            ]
        ]);
    }

    public function returnFormData()
    {
        $sales = Sale::whereNull('original_sale_id')->orderByDesc('created_at')->get(['id', 'invoice_number', 'customer_id', 'total_amount']);

        $lastReturn = Sale::where('invoice_number', 'LIKE', 'RTN-%')->latest()->first();
        $nextId = $lastReturn ? ((int) preg_replace('/[^0-9]/', '', $lastReturn->invoice_number)) + 1 : 1;
        $returnNumber = 'RTN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'sales' => $sales,
            'return_number' => $returnNumber,
        ]);
    }

    public function storeReturn(Request $request)
    {
        $request->validate([
            'original_sale_id' => 'required|exists:sales,id',
            'return_number' => 'required|unique:sales,invoice_number',
            'return_date' => 'required|date',
            'items' => 'required|array',
            'cash_return_amount' => 'nullable|numeric|min:0',
        ]);

        $sale = \DB::transaction(function () use ($request) {
            $originalSale = Sale::with('customer')->findOrFail($request->original_sale_id);

            $totalReturnAmount = 0;
            foreach ($request->items as $item) {
                if ($item['quantity'] > 0) {
                    $totalReturnAmount += ($item['quantity'] * $item['unit_price']);
                }
            }

            $returnSale = Sale::create([
                'customer_id' => $originalSale->customer_id,
                'original_sale_id' => $originalSale->id,
                'invoice_number' => $request->return_number,
                'sale_date' => $request->return_date,
                'total_amount' => -$totalReturnAmount,
                'paid_amount' => -$request->input('cash_return_amount', 0),
                'status' => 'paid',
                'notes' => 'Sales Return for Invoice #' . $originalSale->invoice_number,
                'payment_method' => 'cash',
            ]);

            $originalSale->total_amount -= $totalReturnAmount;
            if ($originalSale->paid_amount >= $originalSale->total_amount) {
                $originalSale->status = 'paid';
            } elseif ($originalSale->paid_amount > 0) {
                $originalSale->status = 'partial';
            } else {
                $originalSale->status = 'unpaid';
            }
            $originalSale->save();

            foreach ($request->items as $item) {
                if ($item['quantity'] > 0) {
                    SaleItem::create([
                        'sale_id' => $returnSale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'description' => 'Return from #' . $originalSale->invoice_number,
                    ]);

                    $product = Product::find($item['product_id']);
                    if ($product) $product->increment('current_stock', $item['quantity']);
                }
            }

            if ($request->cash_return_amount > 0) {
                Payment::create([
                    'amount' => -$request->cash_return_amount,
                    'payment_date' => $request->return_date,
                    'payment_method' => 'cash',
                    'payable_type' => 'App\Models\Customer',
                    'payable_id' => $originalSale->customer_id,
                    'type' => 'out',
                    'transaction_id' => $returnSale->id,
                    'transaction_type' => 'App\Models\Sale',
                    'notes' => 'Cash Return for Return #' . $returnSale->invoice_number,
                ]);
            }

            return $returnSale;
        });

        return response()->json(['message' => 'Sales return processed', 'sale' => $sale], 201);
    }
}
