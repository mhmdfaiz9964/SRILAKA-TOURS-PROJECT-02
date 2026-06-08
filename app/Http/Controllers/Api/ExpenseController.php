<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Bank;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category');
        if ($request->search) { $query->where(function($q) use ($request) { $q->where('reason', 'like', "%{$request->search}%")->orWhere('payer_name', 'like', "%{$request->search}%"); }); }
        if ($request->date) $query->whereDate('expense_date', $request->date);
        if ($request->category_id) $query->where('category_id', $request->category_id);

        $totalAmount = (clone $query)->sum('amount');
        $expenses = $query->latest('expense_date')->paginate($request->get('per_page', 15));
        $categories = ExpenseCategory::all();

        return response()->json(['data' => $expenses->items(), 'meta' => ['current_page' => $expenses->currentPage(), 'last_page' => $expenses->lastPage(), 'total' => $expenses->total()], 'total_amount' => $totalAmount, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate(['reason' => 'required|string', 'amount' => 'required|numeric|min:0', 'expense_date' => 'required|date', 'payment_method' => 'required|in:cash,cheque,bank_transfer']);
        $expense = Expense::create($request->all());
        return response()->json(['message' => 'Expense recorded', 'expense' => $expense->load('category')], 201);
    }

    public function show($id) { return response()->json(Expense::with('category')->findOrFail($id)); }
    public function destroy($id) { Expense::findOrFail($id)->delete(); return response()->json(['message' => 'Expense deleted']); }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:expense_categories,name']);
        $category = ExpenseCategory::create(['name' => $request->name]);
        return response()->json(['message' => 'Category created', 'category' => $category], 201);
    }

    public function getCategories() { return response()->json(ExpenseCategory::all()); }
}
