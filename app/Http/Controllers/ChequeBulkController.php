<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChequeBulkController extends Controller
{
    public function updateBulkStatus(Request $request) 
    {
        $request->validate([
            'cheque_ids' => 'required|array',
            'cheque_ids.*' => 'exists:in_cheques,id',
            'status' => 'required|in:returned,third_party,deposited,realized',
            'transfer_type' => 'nullable|in:third_party,supplier',
            'third_party_name' => 'nullable|required_if:transfer_type,third_party|string',
            'supplier_id' => 'nullable|required_if:transfer_type,supplier|exists:suppliers,id',
        ]);

        $cheques = \App\Models\InCheque::whereIn('id', $request->cheque_ids)->get();

        foreach($cheques as $cheque) {
            $updateData = ['status' => $request->status];
            $baseStatus = $request->status;
            
            // Map realized -> realized (Wait, view sends realized, validate was expecting cleared? 
            // Previous validate said 'cleared', view sent 'realized'. 
            // I updated validation to 'realized' to match view.
            
            // Handle Transfer
            if ($baseStatus === 'third_party') {
                if ($request->transfer_type === 'supplier') {
                    $supplier = \App\Models\Supplier::find($request->supplier_id);
                    $updateData['status'] = 'transferred_to_third_party'; // Keeping system status as transferred
                    $updateData['third_party_name'] = 'Supplier: ' . $supplier->full_name;
                    $cheque->notes .= " | Transferred to Supplier: " . $supplier->full_name;
                    
                    // Create Payment for Supplier
                    \App\Models\Payment::create([
                         'payable_type' => 'App\Models\Supplier',
                         'payable_id' => $supplier->id,
                         'amount' => $cheque->amount,
                         'payment_date' => now(),
                         'payment_method' => 'cheque', // Using 'cheque' so it shows up in T-Ledger logic
                         'payment_cheque_number' => $cheque->cheque_number,
                         // We don't have Original Cheque Date here easily unless we query it, but we can assume today or cheque date.
                         // Let's use the cheque's original date for the reference or today. 
                         // Usually payment date is today (transfer date). 
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
                        'cheque_id' => $cheque->id, // Assuming column is cheque_id or in_cheque_id? 
                        // In InChequeController store method it used: 'in_cheque_id' => $cheque->id
                        'in_cheque_id' => $cheque->id,
                        'third_party_name' => $request->third_party_name,
                        'transfer_date' => now(), // changed from received_date to transfer_date to match InChequeController
                        'status' => 'received'
                    ]);
                }
            } elseif ($baseStatus === 'realized') {
                $updateData['status'] = 'realized';
            } elseif ($baseStatus === 'deposited') {
                $updateData['status'] = 'deposited';
            } elseif ($baseStatus === 'returned') {
                $updateData['status'] = 'returned';
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

        return back()->with('success', 'Cheques updated successfully');
    }
}
