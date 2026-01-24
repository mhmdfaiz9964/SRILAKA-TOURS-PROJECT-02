<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = \App\Models\Category::all();
        // Since we don't have a category view yet, we might return JSON if used via API, 
        // but for now let's assume valid view or JSON for Select2
        if(request()->ajax()) {
            return response()->json($categories);
        }
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name',
            'code' => 'nullable|unique:categories,code',
        ]);

        $category = \App\Models\Category::create($request->all());

        if($request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category created successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Category created successfully');
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
