<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index() { return response()->json(['data' => Category::all()]); }
    public function store(Request $request) { $request->validate(['name' => 'required|string']); $cat = Category::create($request->all()); return response()->json(['message' => 'Category created', 'category' => $cat], 201); }
    public function show($id) { return response()->json(Category::findOrFail($id)); }
    public function update(Request $request, $id) { $cat = Category::findOrFail($id); $cat->update($request->all()); return response()->json(['message' => 'Category updated', 'category' => $cat]); }
    public function destroy($id) { Category::findOrFail($id)->delete(); return response()->json(['message' => 'Category deleted']); }
    public function lookup() { return response()->json(Category::select('id', 'name')->get()); }
}
