<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseInvestor;
use App\Models\Product;
use App\Models\Payment;
use App\Models\OutCheque;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('supplier');

        if ($request->filled('start_date')) $query->where('purchase_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('purchase_date', '<=', $request->end_date);
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->supplier_id);
        if ($request->filled('type')) $query->where('purchase_type', $request->type);

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest': $query->orderBy('created_at'); break;
            case 'highest_amount': $query->orderByDesc('total_amount'); break;
            case 'lowest_amount': $query->orderBy('total_amount'); break;
            default: $query->orderByDesc('created_at');
        }

        $purchases = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $purchases->items(),
            'meta' => [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'total' => $purchases->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'purchase_type' => 'required',
            'purchase_date' => 'required|date',
            'items' => 'required|array',
            'paid_amount' => 'nullable|numeric',
        ]);

        $purchase = \DB::transaction(function () use ($request) {
            $total = $request->input('total_amount');
            $paid = $request->input('paid_amount', 0);

            $status = 'unpaid';
            if ($paid >= $total) $status = 'paid';
            elseif ($paid > 0) $status = 'partial';

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'purchase_type' => $request->purchase_type,
                'invoice_number' => $request->invoice_number,
                'grn_number' => $request->grn_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'broker_cost' => $request->broker_cost ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'loading_cost' => $request->loading_cost ?? 0,
                'unloading_cost' => $request->unloading_cost ?? 0,
                'labour_cost' => $request->labour_cost ?? 0,
                'air_ticket_cost' => $request->air_ticket_cost ?? 0,
                'other_expenses' => $request->other_expenses ?? 0,
                'status' => $status,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $productId = $item['existing_product_id'] ?? null;

                if (empty($productId) && !empty($item['product_name'])) {
                    $existing = Product::where('name', $item['product_name'])->orWhere('code', $item['product_name'])->first();
                    if ($existing) {
                        $productId = $existing->id;
                    } else {
                        $newProduct = Product::create([
                            'name' => $item['product_name'],
                            'code' => strtoupper(substr($item['product_name'], 0, 3) . rand(100, 999)),
                            'cost_price' => $item['cost_price'],
                            'sale_price' => $item['cost_price'] * 1.2,
                            'stock_alert' => $item['stock_alert'] ?? 0,
                            'current_stock' => $item['quantity'],
                        ]);
                        $productId = $newProduct->id;
                    }
                }

                if ($productId) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'description' => $item['description'] ?? null,
                        'cost_price' => $item['cost_price'],
                        'total_price' => $item['total_price'],
                    ]);

                    $product = Product::find($productId);
                    if ($product) {
                        $product->increment('current_stock', $item['quantity']);
                        $product->update(['cost_price' => $item['cost_price']]);
                    }
                }
            }

            if ($request->has('investors') && is_array($request->investors)) {
                foreach ($request->investors as $inv) {
                    if (!empty($inv['name']) && !empty($inv['amount'])) {
                        PurchaseInvestor::create([
                            'purchase_id' => $purchase->id,
                            'investor_name' => $inv['name'],
                            'amount' => $inv['amount'],
                        ]);
                    }
                }
            }

            if ($paid > 0) {
                Payment::create([
                    'amount' => $paid,
                    'payment_date' => $purchase->purchase_date,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'bank_id' => $request->bank_id,
                    'payable_type' => 'App\Models\Supplier',
                    'payable_id' => $purchase->supplier_id,
                    'type' => 'out',
                    'transaction_id' => $purchase->id,
                    'transaction_type' => 'App\Models\Purchase',
                    'notes' => 'Initial payment for Purchase ' . ($purchase->invoice_number ?? $purchase->id),
                    'payment_cheque_number' => $request->cheque_number,
                    'payment_cheque_date' => $request->cheque_date,
                ]);
            }

            if ($request->payment_method === 'cheque' && $paid > 0) {
                OutCheque::create([
                    'cheque_date' => $request->cheque_date,
                    'amount' => $paid,
                    'cheque_number' => $request->cheque_number,
                    'bank_id' => $request->bank_id,
                    'payee_name' => $request->payee_name ?? $purchase->supplier->full_name,
                    'status' => 'sent',
                    'notes' => 'Cheque for Purchase ' . ($purchase->invoice_number ?? $purchase->id),
                ]);
            }

            return $purchase;
        });

        return response()->json(['message' => 'Purchase recorded successfully', 'purchase' => $purchase->load('items.product', 'supplier')], 201);
    }

    public function show($id)
    {
        $purchase = Purchase::with('items.product', 'supplier', 'investors', 'payments')->findOrFail($id);
        return response()->json($purchase);
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        $request->validate([
            'supplier_id' => 'required',
            'purchase_date' => 'required|date',
            'items' => 'required|array',
        ]);

        \DB::transaction(function () use ($request, $purchase) {
            foreach ($purchase->items as $item) {
                if ($item->product) $item->product->decrement('current_stock', $item->quantity);
            }
            $purchase->items()->delete();
            $purchase->investors()->delete();

            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'purchase_type' => $request->purchase_type ?? 'local',
                'purchase_date' => $request->purchase_date,
                'notes' => $request->notes,
                'invoice_number' => $request->invoice_number,
                'grn_number' => $request->grn_number,
                'broker_cost' => $request->broker_cost ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'loading_cost' => $request->loading_cost ?? 0,
                'unloading_cost' => $request->unloading_cost ?? 0,
                'labour_cost' => $request->labour_cost ?? 0,
                'air_ticket_cost' => $request->air_ticket_cost ?? 0,
                'other_expenses' => $request->other_expenses ?? 0,
                'total_amount' => $request->total_amount,
            ]);

            foreach ($request->items as $item) {
                if (!empty($item['existing_product_id'])) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['existing_product_id'],
                        'quantity' => $item['quantity'],
                        'description' => $item['description'] ?? null,
                        'cost_price' => $item['cost_price'],
                        'total_price' => $item['total_price'],
                    ]);
                    $product = Product::find($item['existing_product_id']);
                    if ($product) {
                        $product->increment('current_stock', $item['quantity']);
                        $product->update(['cost_price' => $item['cost_price']]);
                    }
                }
            }

            if ($request->has('investors') && is_array($request->investors)) {
                foreach ($request->investors as $inv) {
                    if (!empty($inv['name']) && !empty($inv['amount'])) {
                        PurchaseInvestor::create([
                            'purchase_id' => $purchase->id,
                            'investor_name' => $inv['name'],
                            'amount' => $inv['amount'],
                        ]);
                    }
                }
            }

            $status = 'unpaid';
            if ($purchase->paid_amount >= $purchase->total_amount) $status = 'paid';
            elseif ($purchase->paid_amount > 0) $status = 'partial';
            $purchase->update(['status' => $status]);
        });

        return response()->json(['message' => 'Purchase updated successfully']);
    }

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->items()->delete();
        $purchase->delete();
        return response()->json(['message' => 'Purchase deleted successfully']);
    }

    public function addPayment(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required',
        ]);

        $amount = $request->amount;
        $purchase->paid_amount += $amount;
        $purchase->status = $purchase->paid_amount >= $purchase->total_amount ? 'paid' : 'partial';
        $purchase->save();

        $chequeId = null;
        if ($request->payment_method === 'cheque') {
            $outCheque = OutCheque::create([
                'cheque_date' => $request->cheque_date,
                'amount' => $amount,
                'cheque_number' => $request->cheque_number,
                'bank_id' => $request->bank_id,
                'payee_name' => $request->payee_name,
                'status' => 'sent',
                'notes' => 'Payment for Purchase ' . ($purchase->invoice_number ?? $purchase->id),
            ]);
            $chequeId = $outCheque->id;
        }

        Payment::create([
            'transaction_id' => $purchase->id,
            'transaction_type' => Purchase::class,
            'payable_id' => $purchase->supplier_id,
            'payable_type' => \App\Models\Supplier::class,
            'type' => 'out',
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'cheque_id' => $chequeId,
            'notes' => $request->notes,
        ]);

        return response()->json(['message' => 'Payment added successfully', 'purchase' => $purchase->fresh()]);
    }
}
