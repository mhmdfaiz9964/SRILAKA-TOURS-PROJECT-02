<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withSum('purchases', 'total_amount')->withSum('payments', 'amount');
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);

        $suppliers = $query->orderByDesc('created_at')->paginate($request->get('per_page', 20));
        $suppliers->getCollection()->transform(function($s) {
            $s->outstanding = max(0, ($s->purchases_sum_total_amount ?? 0) - ($s->payments_sum_amount ?? 0));
            return $s;
        });

        return response()->json([
            'data' => $suppliers->items(),
            'meta' => ['current_page' => $suppliers->currentPage(), 'last_page' => $suppliers->lastPage(), 'total' => $suppliers->total()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['full_name' => 'required']);
        $supplier = Supplier::create($request->all());
        return response()->json(['message' => 'Supplier created', 'supplier' => $supplier], 201);
    }

    public function show($id)
    {
        $supplier = Supplier::withSum('purchases', 'total_amount')->withSum('payments', 'amount')->findOrFail($id);
        $supplier->outstanding = max(0, ($supplier->purchases_sum_total_amount ?? 0) - ($supplier->payments_sum_amount ?? 0));
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $request->validate(['full_name' => 'required']);
        $supplier->update($request->all());
        return response()->json(['message' => 'Supplier updated', 'supplier' => $supplier]);
    }

    public function destroy($id) { Supplier::findOrFail($id)->delete(); return response()->json(['message' => 'Supplier deleted']); }

    public function ledger($id)
    {
        $supplier = Supplier::with('purchases', 'payments')->findOrFail($id);
        $ledger = collect();
        foreach ($supplier->purchases as $purchase) {
            $ledger->push(['date' => $purchase->purchase_date, 'type' => 'purchase', 'description' => 'Purchase #' . ($purchase->invoice_number ?? $purchase->id), 'debit' => $purchase->total_amount, 'credit' => 0]);
        }
        foreach ($supplier->payments as $payment) {
            $ledger->push(['date' => \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d'), 'type' => 'payment', 'description' => 'Payment - ' . ucfirst(str_replace('_', ' ', $payment->payment_method)), 'debit' => 0, 'credit' => $payment->amount]);
        }
        $ledger = $ledger->sortBy('date')->values();
        $balance = 0;
        $ledger = $ledger->map(function($item) use (&$balance) { $balance += ($item['debit'] - $item['credit']); $item['balance'] = $balance; return $item; });
        return response()->json(['supplier' => $supplier->only('id', 'full_name', 'company_name'), 'ledger' => $ledger]);
    }

    public function lookup() { return response()->json(Supplier::where('status', true)->select('id', 'full_name', 'company_name')->get()); }
}
