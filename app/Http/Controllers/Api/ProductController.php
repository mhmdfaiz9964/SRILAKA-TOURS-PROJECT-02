<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);

        $products = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string|unique:products,code',
            'cost_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
        ]);
        $product = Product::create($request->all());
        return response()->json(['message' => 'Product created', 'product' => $product], 201);
    }

    public function show($id)
    {
        return response()->json(Product::with('category')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string|unique:products,code,' . $id,
        ]);
        $product->update($request->all());
        return response()->json(['message' => 'Product updated', 'product' => $product]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function lookup()
    {
        return response()->json(Product::select('id', 'name', 'code', 'sale_price', 'cost_price', 'current_stock', 'units')->get());
    }
}
