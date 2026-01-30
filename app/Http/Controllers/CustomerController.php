<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Customer::withSum('sales', 'total_amount')
            ->withSum('payments', 'amount');

        // Search
        if ($request->search) {
            $query->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%")
                  ->orWhere('mobile_number', 'like', "%{$request->search}%");
        }

        // Sorting
        if ($request->sort) {
            switch ($request->sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest_amount':
                    $query->orderByDesc('sales_sum_total_amount');
                    break;
                case 'lowest_amount':
                    $query->orderBy('sales_sum_total_amount', 'asc');
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

        $customers = $query->paginate(20)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'mobile_number' => 'nullable',
            'credit_limit' => 'nullable|numeric',
            'company_name' => 'nullable',
        ]);

        $customer = \App\Models\Customer::create($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer created successfully.'
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Customer $customer)
    {
        // Load relationships
        $customer->load('sales', 'payments');

        // Build Ledger
        $ledger = collect();

        // Add Sales (Debits)
        foreach ($customer->sales as $sale) {
            $ledger->push([
                'date' => $sale->sale_date,
                'updated_at' => $sale->created_at, // For secondary sort
                'type' => 'invoice',
                'description' => 'Invoice #' . $sale->invoice_number,
                'debit' => $sale->total_amount,
                'credit' => 0,
                'url' => route('sales.show', $sale->id)
            ]);
        }

        // Add Payments (Credits)
        foreach ($customer->payments as $payment) {
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
                'url' => '#' // Payment view if exists
            ]);
        }

        // Sort by Date, then Created At
        $ledger = $ledger->sort(function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['updated_at'] <=> $b['updated_at'];
            }
            return $a['date'] <=> $b['date'];
        });

        return view('customers.show', compact('customer', 'ledger'));
    }

    public function exportLedger(Request $request, \App\Models\Customer $customer)
    {
        $type = $request->get('format', 'pdf');
        
        // Build Ledger (Same logic as show)
        $customer->load('sales', 'payments');
        $ledger = collect();

        foreach ($customer->sales as $sale) {
            $ledger->push([
                'date' => $sale->sale_date,
                'updated_at' => $sale->created_at,
                'type' => 'Invoice',
                'description' => 'Invoice #' . $sale->invoice_number,
                'debit' => $sale->total_amount,
                'credit' => 0,
            ]);
        }

        foreach ($customer->payments as $payment) {
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
                'entity' => $customer,
                'type' => 'Customer',
                'ledger' => $ledger
            ]);
            return $pdf->download('customer_ledger_' . $customer->id . '.pdf');
        }

        // Export Excel (CSV) using StreamedResponse
        $filename = 'customer_ledger_' . $customer->id . '.csv';

        return response()->stream(function () use ($ledger) {
            $handle = fopen('php://output', 'w');
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
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Customer $customer)
    {
        $request->validate([
            'full_name' => 'required',
            'mobile_number' => 'nullable',
            'credit_limit' => 'nullable|numeric',
            'company_name' => 'nullable',
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
