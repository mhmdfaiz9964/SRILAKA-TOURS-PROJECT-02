<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = \App\Models\Supplier::all();
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
            'contact_number' => 'required',
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
