<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChequeBulkController extends Controller
{
    public function updateBulkStatus(Request $request) 
    {
        \Log::info('Entered updateBulkStatus', ['url' => $request->url(), 'method' => $request->method(), 'all' => $request->all()]);
        $request->validate([
            'cheque_ids' => 'required|array',
            'type' => 'required|in:in_cheque,out_cheque,third_party_cheque,cheque', // Added type
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
                'third_party_name' => 'nullable|string',
                'supplier_id' => 'nullable|required_if:transfer_type,supplier|exists:suppliers,id',
            ]);

            $cheques = \App\Models\InCheque::whereIn('id', $chequeIds)->get();
            \Log::info('Bulk Update InCheque: IDs received', ['ids' => $chequeIds]);
            \Log::info('Bulk Update InCheque: Models found', ['count' => $cheques->count()]);

            $count = 0;
            foreach($cheques as $cheque) {
                // Determine new status
                $newStatus = $status;
                if ($status === 'third_party') {
                   $newStatus = 'transferred_to_third_party';
                }

                $cheque->status = $newStatus; // Explicitly set status

                // Handle Side Effects
                if ($status === 'third_party') {
                    if ($request->transfer_type === 'supplier') {
                        $supplier = \App\Models\Supplier::find($request->supplier_id);
                        $cheque->third_party_name = 'Supplier: ' . $supplier->full_name;
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
                        $cheque->third_party_name = $request->third_party_name;
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
                     // Optionally create Returned Cheque Record
                     \App\Models\Cheque::create([
                        'cheque_number' => $cheque->cheque_number,
                        'cheque_date' => $cheque->cheque_date,
                        'bank_id' => $cheque->bank_id,
                        'amount' => $cheque->amount,
                        'payer_name' => $cheque->payer_name,
                        'payment_status' => 'pending',
                        'type' => 'returned',
                        'return_reason' => 'Direct Bulk Return'
                    ]);
                }
                
                $cheque->save(); // Use save() directly
                $count++;
            }

        } elseif ($type === 'out_cheque') {
            $request->validate([
                'cheque_ids.*' => 'exists:out_cheques,id',
                'status' => 'required|in:realized,bounced,sent', // Allowed statuses for bulk update
            ]);

            $cheques = \App\Models\OutCheque::whereIn('id', $chequeIds)->get();
            $count = $cheques->count();

            foreach($cheques as $cheque) {
                $cheque->status = $status;
                $cheque->save();
            }

        } elseif ($type === 'third_party_cheque') {
            $request->validate([
                'cheque_ids.*' => 'exists:third_party_cheques,id',
                'status' => 'required|in:realized,returned,received', // Allowed statuses
            ]);

            $cheques = \App\Models\ThirdPartyCheque::whereIn('id', $chequeIds)->get();
            $count = $cheques->count();

            foreach($cheques as $cheque) {
                $cheque->status = $status;
                $cheque->save();
            }
        } elseif ($type === 'cheque') {
            $request->validate([
                'cheque_ids.*' => 'exists:cheques,id',
                'status' => 'required|in:paid,pending,delete', // Allowed statuses
            ]);

            $cheques = \App\Models\Cheque::whereIn('id', $chequeIds)->get();
            $count = 0;

            if ($status === 'delete') {
                 $count = $cheques->count();
                 foreach($cheques as $cheque) {
                     $cheque->delete();
                 }
                 return back()->with('success', $count . ' Cheques deleted successfully');
            } else {
                foreach($cheques as $cheque) {
                    $cheque->payment_status = $status;
                    $cheque->save();
                    $count++;
                }
            }
        }

        return back()->with('success', $count . ' Cheques updated successfully');
    }
}
