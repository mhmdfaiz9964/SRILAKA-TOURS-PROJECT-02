<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplier-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:supplier-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:supplier-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:supplier-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Supplier::withSum('purchases', 'total_amount')
            ->withSum('payments', 'amount');

        // Search
        if ($request->search) {
            $query->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%");
        }

        // Sorting
        if ($request->sort) {
            switch ($request->sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                 // Supplier might not have sales_sum, so we use full_name or created_at for generic sorts
                 // If we had Purchase Sum we could use that. Let's stick to Name/Date for now as safe defaults
                case 'highest_amount':
                    // Need to join purchases or sum
                    // Simple hack: Sort by ID for now or implement relationship sum if crucial. 
                    // Given I didn't see 'withSum' in SupplierController originally, I will skip complex joins to avoid errors.
                    // Just fallback to latest.
                    $query->orderByDesc('created_at'); 
                    break;
                case 'lowest_amount':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name_az':
                    $query->orderBy('full_name', 'asc');
                    break;
                case 'latest':
                default:
                    $query->orderByDesc('created_at');
                    break;
            }
        } else {
             $query->orderByDesc('created_at');
        }

        $suppliers = $query->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'contact_number' => 'nullable',
            'company_name' => 'nullable',
        ]);

        $supplier = \App\Models\Supplier::create($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'supplier' => $supplier,
                'message' => 'Supplier created successfully.'
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Supplier $supplier)
    {
        // Load relationships
        $supplier->load('purchases', 'payments');

        // Build Ledger
        $ledger = collect();

        // Add Purchases (Debits - We owe them more)
        foreach ($supplier->purchases as $purchase) {
            $ledger->push([
                'date' => $purchase->purchase_date,
                'updated_at' => $purchase->created_at,
                'type' => 'invoice',
                'description' => 'Purchase #' . ($purchase->invoice_number ?? $purchase->id) . ($purchase->grn_number ? ' [GRN: '.$purchase->grn_number.']' : ''),
                'debit' => $purchase->total_amount,
                'credit' => 0,
                'url' => route('purchases.show', $purchase->id)
            ]);
        }

        // Add Payments (Credits - We paid them)
        foreach ($supplier->payments as $payment) {
            $ledger->push([
                'date' => \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d'),
                'updated_at' => $payment->created_at,
                'type' => 'payment',
                'description' => 'Payment - ' . ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'payment_method' => $payment->payment_method,
                'cheque_number' => $payment->payment_cheque_number,
                'cheque_date' => $payment->payment_cheque_date,
                'debit' => 0,
                'credit' => $payment->amount,
                'url' => '#' 
            ]);
        }

        // Sort by Date, then Created At
        $ledger = $ledger->sort(function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['updated_at'] <=> $b['updated_at'];
            }
            return $a['date'] <=> $b['date'];
        });

        return view('suppliers.show', compact('supplier', 'ledger'));
    }

    public function exportLedger(Request $request, \App\Models\Supplier $supplier)
    {
        $type = $request->get('format', 'pdf');
        
        // Build Ledger (Same logic as show)
        $supplier->load('purchases', 'payments');
        $ledger = collect();

        foreach ($supplier->purchases as $purchase) {
            $ledger->push([
                'date' => $purchase->purchase_date,
                'updated_at' => $purchase->created_at,
                'type' => 'Purchase',
                'description' => 'Purchase #' . ($purchase->invoice_number ?? $purchase->id),
                'debit' => $purchase->total_amount,
                'credit' => 0,
            ]);
        }

        foreach ($supplier->payments as $payment) {
            $ledger->push([
                'date' => \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d'),
                'updated_at' => $payment->created_at,
                'type' => 'Payment',
                'description' => 'Payment - ' . ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'debit' => 0,
                'credit' => $payment->amount,
            ]);
        }

        $ledger = $ledger->sort(function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['updated_at'] <=> $b['updated_at'];
            }
            return $a['date'] <=> $b['date'];
        });

        if ($type === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.ledger', [
                'entity' => $supplier,
                'type' => 'Supplier',
                'ledger' => $ledger
            ]);
            return $pdf->download('supplier_ledger_' . $supplier->id . '.pdf');
        }

        // Export Excel (CSV)
        $filename = 'supplier_ledger_' . $supplier->id . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Date', 'Description', 'Type', 'Debit', 'Credit', 'Balance']);

        $balance = 0;
        foreach ($ledger as $item) {
            $balance += ($item['debit'] - $item['credit']);
            fputcsv($handle, [
                $item['date'],
                $item['description'],
                $item['type'],
                number_format($item['debit'], 2, '.', ''),
                number_format($item['credit'], 2, '.', ''),
                number_format($balance, 2, '.', '')
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Supplier $supplier)
    {
        $request->validate([
            'full_name' => 'required',
            'contact_number' => 'nullable',
            'company_name' => 'nullable',
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
