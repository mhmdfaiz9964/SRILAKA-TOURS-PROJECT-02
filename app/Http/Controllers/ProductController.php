<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Product::with('category');
        
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_main_product') && $request->is_main_product != '') {
            $query->where('is_main_product', $request->is_main_product);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'latest':
                    $query->latest();
                    break;
                case 'oldest':
                    $query->oldest();
                    break;
                case 'name_az':
                    $query->orderBy('name');
                    break;
                case 'highest_price':
                    $query->orderByDesc('sale_price');
                    break;
                case 'lowest_price':
                    $query->orderBy('sale_price');
                    break;
                case 'highest_stock':
                    $query->orderByDesc('stock_alert');
                    break;
                case 'lowest_stock':
                    $query->orderBy('stock_alert');
                    break;
                default:
                    $query->latest();
            }
        } else {
             $query->latest();
        }

        $products = $query->get();
        // Calculate Total Cost Value (Current Stock * Cost Price)
        // Using stock_alert as current_stock based on SaleController logic
        $totalCostValue = $products->sum(function($product) {
            return $product->stock_alert * $product->cost_price;
        });
        
        // Sold Stock logic will be handled in view or here. 
        // View is better for per-product, but Total Cost is here.

        return view('products.index', compact('products', 'totalCostValue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\Category::all();
        $mainProducts = \App\Models\Product::where('is_main_product', true)->get();
        return view('products.create', compact('categories', 'mainProducts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'nullable', // Made nullable as per "Remove Product Code" (might act as internal ID if needed, or just ignore)
            'category_id' => 'nullable',
            'is_main_product' => 'boolean',
            'parent_product_id' => 'nullable|required_if:is_main_product,0|exists:products,id',
        ]);

        \App\Models\Product::create($request->all());

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
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
        $product = \App\Models\Product::findOrFail($id);
        $categories = \App\Models\Category::all();
        $mainProducts = \App\Models\Product::where('is_main_product', true)->where('id', '!=', $id)->get();
        return view('products.edit', compact('product', 'categories', 'mainProducts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'category_id' => 'nullable',
            'is_main_product' => 'boolean',
            'parent_product_id' => 'nullable|required_if:is_main_product,0|exists:products,id',
        ]);

        $product = \App\Models\Product::findOrFail($id);
        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = \App\Models\Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
