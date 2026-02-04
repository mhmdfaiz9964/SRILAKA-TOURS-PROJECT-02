<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Validation;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// 1. Get an existing product or create one
$product = Product::first();
if (!$product) {
    $product = Product::create([
        'name' => 'Test Product',
        'code' => 'TEST001',
        'cost_price' => 100,
        'sale_price' => 120,
        'current_stock' => 50,
        'stock_alert' => 10
    ]);
}

$initialStock = $product->current_stock;
echo "Initial Stock for {$product->name} (ID: {$product->id}): {$initialStock}\n";

// 2. Simulate Purchase Request Data
$purchaseData = [
    'supplier_id' => \App\Models\Supplier::first()->id ?? \App\Models\Supplier::create(['full_name'=>'Test Supp', 'status'=>1])->id,
    'purchase_type' => 'local',
    'purchase_date' => date('Y-m-d'),
    'items' => [
        [
            'existing_product_id' => $product->id,
            'quantity' => 100, // WE WANT TO ADD 100
            'cost_price' => 100,
            'total_price' => 10000,
        ]
    ],
    'total_amount' => 10000
];

// 3. Execute Logic (Mimic Controller Store)
DB::transaction(function () use ($purchaseData) {
    $purchase = Purchase::create([
        'supplier_id' => $purchaseData['supplier_id'],
        'purchase_type' => $purchaseData['purchase_type'],
        'purchase_date' => $purchaseData['purchase_date'],
        'total_amount' => $purchaseData['total_amount'],
        'status' => 'unpaid'
    ]);

    foreach ($purchaseData['items'] as $item) {
        $productId = $item['existing_product_id'];
        
        // Controller Logic Copy-Paste (Simplified)
        if ($productId) {
            \App\Models\PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'cost_price' => $item['cost_price'],
                'total_price' => $item['total_price'],
            ]);

            $prod = Product::find($productId);
            // THE CRITICAL LINE
            $prod->increment('current_stock', $item['quantity']);
        }
    }
});

// 4. Verify Final Stock
$finalStock = $product->fresh()->current_stock;
echo "Final Stock: {$finalStock}\n";

if ($finalStock == $initialStock + 100) {
    echo "SUCCESS: Stock incremented by 100.\n";
} else {
    echo "FAILURE: Stock mismatch.\n";
}
