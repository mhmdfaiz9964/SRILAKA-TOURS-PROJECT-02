<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = \App\Models\Purchase::with('supplier')->orderByDesc('created_at')->get();
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = \App\Models\Supplier::where('status', true)->get();
        $products = \App\Models\Product::all();
        $banks = \App\Models\Bank::all();
        return view('purchases.create', compact('suppliers', 'products', 'banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'purchase_date' => 'required|date',
            'items' => 'required|array',
            'paid_amount' => 'nullable|numeric',
        ]);

        \DB::transaction(function () use ($request) {
            // 1. Calculations
            $total = $request->input('total_amount');
            $paid = $request->input('paid_amount', 0);
            
            $status = 'unpaid';
            if ($paid >= $total) $status = 'paid';
            elseif ($paid > 0) $status = 'partial';

            // 2. Create Purchase
            $purchase = \App\Models\Purchase::create([
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'status' => $status,
                'notes' => $request->notes,
            ]);

            // 3. Process Items
            foreach ($request->items as $item) {
                $productId = $item['existing_product_id'];

                // Auto Create Product if Not Exists
                if (empty($productId) && !empty($item['product_name'])) {
                    // Check by code or name just in case
                    $existing = \App\Models\Product::where('name', $item['product_name'])->orWhere('code', $item['product_name'])->first();
                    if ($existing) {
                        $productId = $existing->id;
                    } else {
                        $newProduct = \App\Models\Product::create([
                            'name' => $item['product_name'],
                            'code' => strtoupper(substr($item['product_name'], 0, 3) . rand(100, 999)), // Auto gen code
                            'cost_price' => $item['cost_price'],
                            'sale_price' => $item['cost_price'] * 1.2, // Default markup 20%
                            'stock_alert' => $item['quantity'], // Set initial stock
                        ]);
                        $productId = $newProduct->id;
                    }
                }

                if ($productId) {
                    // Create Purchase Item
                    \App\Models\PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'cost_price' => $item['cost_price'],
                        'total_price' => $item['total_price'],
                    ]);

                    // Update Stock & Cost
                    $product = \App\Models\Product::find($productId);
                    if ($product) {
                        $product->increment('stock_alert', $item['quantity']); // Using stock_alert as stock per current db state
                        $product->update(['cost_price' => $item['cost_price']]); // Update latest cost
                    }
                }
            }
            
            // 4. Handle Cheque Payment (Out Cheque)
            if ($request->payment_method === 'cheque' && $paid > 0) {
                \App\Models\OutCheque::create([
                    'cheque_date' => $request->cheque_date,
                    'amount' => $paid,
                    'cheque_number' => $request->cheque_number,
                    'bank_id' => $request->bank_id,
                    'payee_name' => $request->payee_name ?? config('app.name'),
                    'status' => 'sent',
                    'notes' => 'Payment for Purchase ' . ($purchase->invoice_number ?? $purchase->id),
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
