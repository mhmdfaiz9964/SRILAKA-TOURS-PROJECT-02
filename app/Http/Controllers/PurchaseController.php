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
            'purchase_type' => 'required',
            'purchase_date' => 'required|date',
            'grn_number' => 'nullable|string',
            'items' => 'required|array',
            'items.*.description' => 'nullable|string',
            'investors' => 'nullable|array',
            'investors.*.name' => 'required_with:investors|string',
            'investors.*.amount' => 'required_with:investors|numeric',
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
                'purchase_type' => $request->purchase_type,
                'invoice_number' => $request->invoice_number,
                'grn_number' => $request->grn_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'broker_cost' => $request->broker_cost ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'duty_cost' => $request->duty_cost ?? 0,
                'kuli_cost' => $request->kuli_cost ?? 0,
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
                        'description' => $item['description'] ?? null,
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
            


            // 3.5. Process Investors
            if ($request->has('investors') && is_array($request->investors)) {
                foreach ($request->investors as $inv) {
                     if (!empty($inv['name']) && !empty($inv['amount'])) {
                        \App\Models\PurchaseInvestor::create([
                            'purchase_id' => $purchase->id,
                            'investor_name' => $inv['name'],
                            'amount' => $inv['amount'],
                        ]);
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
    public function show($id)
    {
         $purchase = \App\Models\Purchase::with('items.product', 'supplier')->findOrFail($id);
         return view('purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
         $purchase = \App\Models\Purchase::with('items')->findOrFail($id);
         $suppliers = \App\Models\Supplier::where('status', true)->get();
         $products = \App\Models\Product::all();
         $banks = \App\Models\Bank::all();
         return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'banks'));
    }

    public function update(Request $request, $id)
    {
        $purchase = \App\Models\Purchase::findOrFail($id);
        
        $request->validate([
            'supplier_id' => 'required',
            'purchase_date' => 'required|date',
        ]);
        
        $purchase->update([
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
            'invoice_number' => $request->invoice_number,
            'grn_number' => $request->grn_number,
        ]);
        
        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully');
    }

    public function destroy($id)
    {
        $purchase = \App\Models\Purchase::findOrFail($id);
        $purchase->items()->delete();
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully');
    }
}
