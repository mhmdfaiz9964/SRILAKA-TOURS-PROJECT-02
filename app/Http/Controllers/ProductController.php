<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
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

        $products = $query->get();
        $categories = \App\Models\Category::all();

        return view('products.index', compact('products', 'categories'));
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
            'code' => 'required|unique:products,code',
            'category_id' => 'nullable|exists:categories,id',
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
            'code' => 'required|unique:products,code,' . $id,
            'category_id' => 'nullable|exists:categories,id',
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
