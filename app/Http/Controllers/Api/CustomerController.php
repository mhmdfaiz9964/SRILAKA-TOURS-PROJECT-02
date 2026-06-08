<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Payment;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withSum('sales', 'total_amount')->withSum('payments', 'amount');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%")
                  ->orWhere('mobile_number', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->outstanding_only == '1') {
            $query->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE sales.customer_id = customers.id) > (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.payable_id = customers.id AND payments.payable_type = "App\\\\Models\\\\Customer")');
        }

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest': $query->orderBy('created_at'); break;
            case 'name_az': $query->orderBy('full_name'); break;
            case 'highest_amount': $query->orderByDesc('sales_sum_total_amount'); break;
            default: $query->orderByDesc('created_at');
        }

        $totalOutstanding = Customer::withSum('sales', 'total_amount')
            ->withSum('payments', 'amount')->get()
            ->sum(fn($c) => max(0, ($c->sales_sum_total_amount ?? 0) - ($c->payments_sum_amount ?? 0)));

        $customers = $query->paginate($request->get('per_page', 20));

        $customers->getCollection()->transform(function($c) {
            $c->outstanding = max(0, ($c->sales_sum_total_amount ?? 0) - ($c->payments_sum_amount ?? 0));
            return $c;
        });

        return response()->json([
            'data' => $customers->items(),
            'meta' => ['current_page' => $customers->currentPage(), 'last_page' => $customers->lastPage(), 'total' => $customers->total()],
            'total_outstanding' => $totalOutstanding,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['full_name' => 'required', 'credit_limit' => 'nullable|numeric']);
        $customer = Customer::create($request->all());
        return response()->json(['message' => 'Customer created', 'customer' => $customer], 201);
    }

    public function show($id)
    {
        $customer = Customer::withSum('sales', 'total_amount')->withSum('payments', 'amount')->findOrFail($id);
        $customer->outstanding = max(0, ($customer->sales_sum_total_amount ?? 0) - ($customer->payments_sum_amount ?? 0));
        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate(['full_name' => 'required']);
        $customer->update($request->all());
        return response()->json(['message' => 'Customer updated', 'customer' => $customer]);
    }

    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();
        return response()->json(['message' => 'Customer deleted']);
    }

    public function ledger($id)
    {
        $customer = Customer::with('sales', 'payments')->findOrFail($id);
        $ledger = collect();

        foreach ($customer->sales as $sale) {
            $ledger->push([
                'date' => $sale->sale_date,
                'type' => 'invoice',
                'description' => 'Invoice #' . $sale->invoice_number,
                'debit' => $sale->total_amount,
                'credit' => 0,
            ]);
        }
        foreach ($customer->payments as $payment) {
            $ledger->push([
                'date' => \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d'),
                'type' => 'payment',
                'description' => 'Payment - ' . ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'debit' => 0,
                'credit' => $payment->amount,
            ]);
        }

        $ledger = $ledger->sortBy('date')->values();
        $balance = 0;
        $ledger = $ledger->map(function($item) use (&$balance) {
            $balance += ($item['debit'] - $item['credit']);
            $item['balance'] = $balance;
            return $item;
        });

        return response()->json(['customer' => $customer->only('id', 'full_name', 'company_name'), 'ledger' => $ledger]);
    }

    public function lookup()
    {
        return response()->json(Customer::where('status', true)->select('id', 'full_name', 'company_name')->get());
    }
}
