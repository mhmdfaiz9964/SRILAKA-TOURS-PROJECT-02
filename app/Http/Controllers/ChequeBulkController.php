<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChequeBulkController extends Controller
{
    public function updateBulkStatus(Request $request) 
    {
        $request->validate([
            'cheque_ids' => 'required|array',
            'type' => 'required|in:in_cheque,out_cheque,third_party_cheque', // Added type
            'status' => 'required|string', // Validation depends on type, so basic string here
        ]);

        $type = $request->type;
        $chequeIds = $request->cheque_ids;
        $status = $request->status;

        if ($type === 'in_cheque') {
             $request->validate([
                'cheque_ids.*' => 'exists:in_cheques,id',
                'status' => 'required|in:returned,third_party,deposited,realized',
                'transfer_type' => 'nullable|in:third_party,supplier',
                'third_party_name' => 'nullable|required_if:transfer_type,third_party|string',
                'supplier_id' => 'nullable|required_if:transfer_type,supplier|exists:suppliers,id',
            ]);

            $cheques = \App\Models\InCheque::whereIn('id', $chequeIds)->get();

            foreach($cheques as $cheque) {
                $updateData = ['status' => $status];
                
                // Handle Transfer
                if ($status === 'third_party') {
                    if ($request->transfer_type === 'supplier') {
                        $supplier = \App\Models\Supplier::find($request->supplier_id);
                        $updateData['status'] = 'transferred_to_third_party'; 
                        $updateData['third_party_name'] = 'Supplier: ' . $supplier->full_name;
                        $cheque->notes .= " | Transferred to Supplier: " . $supplier->full_name;
                        
                        // Create Payment for Supplier
                        \App\Models\Payment::create([
                             'payable_type' => 'App\Models\Supplier',
                             'payable_id' => $supplier->id,
                             'amount' => $cheque->amount,
                             'payment_date' => now(),
                             'payment_method' => 'cheque', 
                             'payment_cheque_number' => $cheque->cheque_number,
                             'payment_cheque_date' => $cheque->cheque_date, 
                             'type' => 'out',
                             'notes' => "Assigned from In Cheque #{$cheque->cheque_number}"
                        ]);

                    } else {
                        $updateData['status'] = 'transferred_to_third_party';
                        $updateData['third_party_name'] = $request->third_party_name;
                        $cheque->notes .= " | Transferred to: " . $request->third_party_name;
                        
                        // Create ThirdPartyCheque entry
                        \App\Models\ThirdPartyCheque::create([
                            'in_cheque_id' => $cheque->id,
                            'third_party_name' => $request->third_party_name,
                            'transfer_date' => now(), 
                            'status' => 'received'
                        ]);
                    }
                } elseif ($status === 'returned') {
                     // Optionally create Returned Cheque Record like InChequeController does
                     \App\Models\Cheque::create([
                        'cheque_number' => $cheque->cheque_number,
                        'cheque_date' => $cheque->cheque_date,
                        'bank_id' => $cheque->bank_id,
                        'amount' => $cheque->amount,
                        'payer_name' => $cheque->payer_name,
                        'payment_status' => 'pending',
                        'return_reason' => 'Direct Bulk Return'
                    ]);
                }
                
                $cheque->update($updateData);
            }

        } elseif ($type === 'out_cheque') {
            $request->validate([
                'cheque_ids.*' => 'exists:out_cheques,id',
                'status' => 'required|in:realized,bounced,sent', // Allowed statuses for bulk update
            ]);

            $cheques = \App\Models\OutCheque::whereIn('id', $chequeIds)->get();

            foreach($cheques as $cheque) {
                $cheque->update(['status' => $status]);
            }

        } elseif ($type === 'third_party_cheque') {
            $request->validate([
                'cheque_ids.*' => 'exists:third_party_cheques,id',
                'status' => 'required|in:realized,returned,received', // Allowed statuses
            ]);

            $cheques = \App\Models\ThirdPartyCheque::whereIn('id', $chequeIds)->get();

            foreach($cheques as $cheque) {
                $cheque->update(['status' => $status]);
            }
        }

        return back()->with('success', 'Cheques updated successfully');
    }
}
