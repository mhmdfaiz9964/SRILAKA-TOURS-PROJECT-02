<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchase-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:purchase-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Purchase::with('supplier');

        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('type')) {
            $query->where('purchase_type', $request->type);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'latest':
                    $query->orderByDesc('purchases.created_at');
                    break;
                case 'oldest':
                    $query->orderBy('purchases.created_at');
                    break;
                case 'highest_amount':
                    $query->orderByDesc('purchases.total_amount');
                    break;
                case 'lowest_amount':
                    $query->orderBy('purchases.total_amount');
                    break;
                case 'name_az':
                    $query->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                          ->orderBy('suppliers.full_name')
                          ->select('purchases.*');
                    break;
                default:
                    $query->orderByDesc('purchases.created_at');
            }
        } else {
             $query->orderByDesc('purchases.created_at');
        } 

        $purchases = $query->paginate(10);
        $suppliers = \App\Models\Supplier::select('id', 'full_name')->orderBy('full_name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
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
            'cheque_number' => 'nullable|required_if:payment_method,cheque|digits:6',
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
                'loading_cost' => $request->loading_cost ?? 0,
                'unloading_cost' => $request->unloading_cost ?? 0,
                'labour_cost' => $request->labour_cost ?? 0,
                'air_ticket_cost' => $request->air_ticket_cost ?? 0,
                'other_expenses' => $request->other_expenses ?? 0,
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
                            'stock_alert' => 10, // Default alert
                            'current_stock' => $item['quantity'], // Set initial stock
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
                        $product->increment('current_stock', $item['quantity']);
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
            
            // 4. Create Payment Record (Transaction Log)
            if ($paid > 0) {
                \App\Models\Payment::create([
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

            // 5. Handle Cheque Payment (Out Cheque)
            if ($request->payment_method === 'cheque' && $paid > 0) {
                \App\Models\OutCheque::create([
                    'cheque_date' => $request->cheque_date,
                    'amount' => $paid,
                    'cheque_number' => $request->cheque_number,
                    'bank_id' => $request->bank_id,
                    'payee_name' => $request->payee_name ?? $purchase->supplier->full_name,
                    'status' => 'sent',
                    'notes' => 'Cheque for Purchase ' . ($purchase->invoice_number ?? $purchase->id),
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
         $purchase = \App\Models\Purchase::with('items.product', 'supplier', 'investors', 'payments')->findOrFail($id);
         $banks = \App\Models\Bank::all();
         return view('purchases.show', compact('purchase', 'banks'));
    }

    public function addPayment(Request $request, $id)
    {
        $purchase = \App\Models\Purchase::findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required',
            // Conditional validation for cheque
            'cheque_number' => 'required_if:payment_method,cheque|nullable|digits:6',
            'cheque_date' => 'required_if:payment_method,cheque|nullable|date',
            'bank_id' => 'required_if:payment_method,cheque|nullable|exists:banks,id',
            'payee_name' => 'required_if:payment_method,cheque|nullable|string',
        ]);

        $amount = $request->amount;
        
        // Update Paid Amount
        $purchase->paid_amount += $amount;
        if($purchase->paid_amount >= $purchase->total_amount) {
            $purchase->status = 'paid';
        } else {
            $purchase->status = 'partial';
        }
        $purchase->save();

        // Handle Cheque Creation (Out Cheque for Purchase)
        $chequeId = null;
        if($request->payment_method === 'cheque') {
            $outCheque = \App\Models\OutCheque::create([
                'cheque_date' => $request->cheque_date,
                'amount' => $amount,
                'cheque_number' => $request->cheque_number,
                'bank_id' => $request->bank_id,
                'payee_name' => $request->payee_name,
                'status' => 'sent',
                'notes' => 'Payment for Purchase ' . ($purchase->invoice_number ?? $purchase->id) . '. ' . $request->notes,
            ]);
            // Note: Since Payment model has cheque_id, we might need a way to differentiate in/out cheques if needed.
            // But usually we just store the ID and the context tells us.
            $chequeId = $outCheque->id;
        }

        // Register Payment
        \App\Models\Payment::create([
            'transaction_id' => $purchase->id,
            'transaction_type' => \App\Models\Purchase::class,
            'payable_id' => $purchase->supplier_id,
            'payable_type' => \App\Models\Supplier::class,
            'type' => 'out', // Outgoing payment
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'cheque_id' => $chequeId,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Payment added successfully');
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
            'items' => 'required|array',
        ]);

        \DB::transaction(function () use ($request, $purchase) {
            // 1. Reverse old stock impact
            foreach ($purchase->items as $item) {
                if ($item->product) {
                    $item->product->decrement('current_stock', $item->quantity);
                }
            }
            
            // 2. Clear old items
            $purchase->items()->delete();

            // 3. Update Purchase core details
            $purchase->update([
                'supplier_id' => $request->supplier_id,
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

            // 4. Record new items and apply stock
            foreach ($request->items as $item) {
                if (!empty($item['existing_product_id'])) {
                    \App\Models\PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['existing_product_id'],
                        'quantity' => $item['quantity'],
                        'description' => $item['description'] ?? null,
                        'cost_price' => $item['cost_price'],
                        'total_price' => $item['total_price'],
                    ]);

                    $product = \App\Models\Product::find($item['existing_product_id']);
                    if ($product) {
                        $product->increment('current_stock', $item['quantity']);
                        $product->update(['cost_price' => $item['cost_price']]);
                    }
                }
            }
            
            // 5. Update Status based on current paid_amount
            $status = 'unpaid';
            if ($purchase->paid_amount >= $purchase->total_amount) $status = 'paid';
            elseif ($purchase->paid_amount > 0) $status = 'partial';
            $purchase->update(['status' => $status]);
        });
        
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
