<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Bank;
use Illuminate\Http\Request;

use App\Models\ExpenseCategory;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('reason', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%")
                  ->orWhere('payer_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->date) {
            $query->whereDate('expense_date', $request->date);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $totalAmount = $query->sum('amount');
        
        if ($request->has('export')) {
            $expenses = $query->get();
            if ($request->export == 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ExpensesExport($expenses), 'expenses_export_' . now()->format('YmdHis') . '.xlsx');
            } elseif ($request->export == 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('expenses.pdf', compact('expenses', 'totalAmount'));
                return $pdf->download('expenses_export_' . now()->format('YmdHis') . '.pdf');
            }
        }

        $expenses = $query->latest('expense_date')->paginate(10);
        $categories = ExpenseCategory::all();
        $banks = Bank::all();

        return view('expenses.index', compact('expenses', 'categories', 'totalAmount', 'banks'));
    }

    public function create()
    {
        $banks = Bank::all();
        $categories = ExpenseCategory::all();
        return view('expenses.create', compact('banks', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category_id' => 'nullable|exists:expense_categories,id',
            'payment_method' => 'required|in:cash,cheque,bank_transfer',
            'cheque_number' => 'nullable|required_if:payment_method,cheque|digits:6',
            'cheque_date' => 'nullable|required_if:payment_method,cheque|date',
            'bank_id' => 'nullable|required_if:payment_method,cheque|exists:banks,id',
            'payer_name' => 'nullable|required_if:payment_method,cheque|string',
        ]);

        Expense::create($request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:expense_categories,name|max:255'
        ]);

        $category = ExpenseCategory::create(['name' => $request->name]);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully');
    }
}
