<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Bank;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query();

        if ($request->search) {
            $query->where('reason', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%")
                  ->orWhere('payer_name', 'like', "%{$request->search}%");
        }

        if ($request->date) {
            $query->whereDate('expense_date', $request->date);
        }

        $expenses = $query->latest('expense_date')->paginate(10);

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $banks = Bank::all();
        return view('expenses.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer',
            'cheque_number' => 'nullable|required_if:payment_method,cheque|size:6',
            'cheque_date' => 'nullable|required_if:payment_method,cheque|date',
            'bank_id' => 'nullable|required_if:payment_method,cheque|exists:banks,id',
            'payer_name' => 'nullable|required_if:payment_method,cheque|string', // Validating payer_name if cheque
        ]);

        Expense::create($request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully');
    }
}
