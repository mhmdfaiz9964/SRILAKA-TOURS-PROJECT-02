<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cheque_id' => 'required|exists:cheques,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $payment = \App\Models\Payment::create($request->all());
        
        $cheque = $payment->cheque;
        $totalPaid = $cheque->payments()->sum('amount');

        if ($totalPaid >= $cheque->amount) {
            $cheque->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $cheque->update(['payment_status' => 'partial paid']);
        }

        return back()->with('success', 'Payment recorded successfully');
    }
}
