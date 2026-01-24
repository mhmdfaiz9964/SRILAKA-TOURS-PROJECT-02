<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required',
            'payable_type' => 'nullable|string',
            'payable_id' => 'nullable|integer',
        ]);

        \DB::transaction(function () use ($request) {
            // 1. Create Payment Record
            $payment = \App\Models\Payment::create([
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'bank_id' => $request->bank_id,
                'payable_type' => $request->payable_type, // 'App\Models\Customer' etc
                'payable_id' => $request->payable_id,
                'type' => $request->type ?? 'in', // Default 'in'
                'cheque_id' => $request->cheque_id, // If existing cheque payment
            ]);

            // 2. Handle Cheque Creation (if payment method is cheque)
            if ($request->payment_method === 'cheque') {
                if ($request->payable_type == 'App\Models\Customer') {
                    \App\Models\InCheque::create([
                        'cheque_date' => $request->payment_cheque_date,
                        'amount' => $request->amount,
                        'cheque_number' => $request->payment_cheque_number,
                        'bank_id' => $request->payment_bank_id ?? $request->bank_id,
                        'payer_name' => $request->payer_name,
                        'status' => 'received',
                        'notes' => 'Payment from ' . $request->payer_name,
                    ]);
                } elseif ($request->payable_type == 'App\Models\Supplier') {
                     \App\Models\OutCheque::create([
                        'cheque_date' => $request->payment_cheque_date,
                        'amount' => $request->amount,
                        'cheque_number' => $request->payment_cheque_number,
                        'bank_id' => $request->payment_bank_id ?? $request->bank_id,
                        'payee_name' => $request->payee_name,
                        'status' => 'sent',
                        'notes' => 'Payment to ' . $request->payee_name,
                    ]);
                }
            }

            // 3. Update related cheque status if this payment was FOR a cheque (user flow via ChequeController usually handles this but good to have)
            if ($request->cheque_id) {
                $cheque = \App\Models\Cheque::find($request->cheque_id);
                if($cheque) {
                     $totalPaid = $cheque->payments()->sum('amount');
                     if ($totalPaid >= $cheque->amount) {
                         $cheque->update(['payment_status' => 'paid']);
                     } elseif ($totalPaid > 0) {
                         $cheque->update(['payment_status' => 'partial paid']);
                     }
                }
            }
        });

        return back()->with('success', 'Payment recorded successfully');
    }
}
