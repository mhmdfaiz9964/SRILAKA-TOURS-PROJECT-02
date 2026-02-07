<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ChequeBulkController extends Controller
{
    public function updateBulkStatus(Request $request) 
    {
        Log::info('Entered updateBulkStatus (Combined)', [
            'url' => $request->url(),
            'method' => $request->method(),
            'all' => $request->all()
        ]);

        if ($request->isMethod('get')) {
            Log::warning('GET request hit bulk update endpoint. Redirecting back.');
            return redirect()->back();
        }

        try {
            $request->validate([
                'cheque_ids' => 'required|array',
                'type' => 'required|in:in_cheque,out_cheque,third_party_cheque,cheque',
                'status' => 'required|string',
            ]);

            $type = $request->type;
            $chequeIds = $request->cheque_ids;
            $status = $request->status;
            $count = 0;

            return DB::transaction(function() use ($request, $type, $chequeIds, $status) {
                $count = 0;
                $skipped = 0;

                if ($type === 'in_cheque') {
                    $request->validate([
                        'cheque_ids.*' => 'exists:in_cheques,id',
                        'status' => 'required|in:returned,third_party,deposited,realized',
                        'transfer_type' => 'nullable|in:third_party,supplier',
                        'third_party_name' => 'nullable|string',
                        'supplier_id' => 'nullable|required_if:transfer_type,supplier|exists:suppliers,id',
                    ]);

                    $cheques = \App\Models\InCheque::whereIn('id', $chequeIds)->get();
                    
                    foreach($cheques as $cheque) {
                        if ($cheque->status === 'returned') {
                            $skipped++;
                            continue;
                        }
                        
                        $newStatus = $status;
                        if ($status === 'third_party') {
                           $newStatus = 'transferred_to_third_party';
                        }

                        $cheque->status = $newStatus;

                        if ($status === 'third_party') {
                            if ($request->transfer_type === 'supplier') {
                                $supplier = \App\Models\Supplier::find($request->supplier_id);
                                $cheque->third_party_name = 'Supplier: ' . $supplier->full_name;
                                $cheque->notes .= " | Transferred to Supplier: " . $supplier->full_name;
                                
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

                                \App\Models\ThirdPartyCheque::create([
                                    'in_cheque_id' => $cheque->id,
                                    'third_party_name' => 'Supplier: ' . $supplier->full_name,
                                    'transfer_date' => now(), 
                                    'status' => 'received'
                                ]);
                            } else {
                                $cheque->third_party_name = $request->third_party_name;
                                $cheque->notes .= " | Transferred to: " . $request->third_party_name;
                                
                                \App\Models\ThirdPartyCheque::create([
                                    'in_cheque_id' => $cheque->id,
                                    'third_party_name' => $request->third_party_name ?: 'Unknown',
                                    'transfer_date' => now(), 
                                    'status' => 'received'
                                ]);
                            }
                        } elseif ($status === 'returned') {
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
                        $cheque->save();
                        $count++;
                    }

                    $msg = "$count cheque(s) updated successfully.";
                    if ($skipped > 0) $msg .= " $skipped returned cheque(s) were skipped.";
                    return redirect()->back()->with('success', $msg);

                } elseif ($type === 'out_cheque') {
                    $request->validate([
                        'cheque_ids.*' => 'exists:out_cheques,id',
                        'status' => 'required|in:realized,bounced,sent',
                    ]);

                    $cheques = \App\Models\OutCheque::whereIn('id', $chequeIds)->get();
                    foreach($cheques as $cheque) {
                        $cheque->status = $status;
                        $cheque->save();
                        $count++;
                    }
                    return redirect()->back()->with('success', "$count cheque(s) updated successfully.");

                } elseif ($type === 'third_party_cheque') {
                    $request->validate([
                        'cheque_ids.*' => 'exists:third_party_cheques,id',
                        'status' => 'required|in:realized,returned,received',
                    ]);

                    $cheques = \App\Models\ThirdPartyCheque::whereIn('id', $chequeIds)->with('inCheque')->get();
                    foreach($cheques as $cheque) {
                        $cheque->status = $status;
                        $cheque->save();
                        
                        if ($status === 'returned') {
                            $cheque->inCheque->update(['status' => 'returned']);
                            \App\Models\Cheque::create([
                                'cheque_number' => $cheque->inCheque->cheque_number,
                                'cheque_date' => $cheque->inCheque->cheque_date,
                                'bank_id' => $cheque->inCheque->bank_id,
                                'amount' => $cheque->inCheque->amount,
                                'payer_name' => $cheque->inCheque->payer_name,
                                'payment_status' => 'pending',
                                'payee_name' => $cheque->third_party_name,
                                'return_reason' => 'Bulk Returned from 3rd Party'
                            ]);
                        } elseif ($status === 'realized') {
                            $cheque->inCheque->update(['status' => 'realized']);
                        }
                        $count++;
                    }
                    return redirect()->back()->with('success', "$count cheque(s) updated successfully.");

                } elseif ($type === 'cheque') {
                    $request->validate([
                        'cheque_ids.*' => 'exists:cheques,id',
                        'status' => 'required|in:paid,pending,delete',
                    ]);

                    $cheques = \App\Models\Cheque::whereIn('id', $chequeIds)->get();
                    if ($status === 'delete') {
                         $count = $cheques->count();
                         \App\Models\Cheque::whereIn('id', $chequeIds)->delete();
                         return back()->with('success', $count . ' Cheques deleted successfully');
                    } else {
                        foreach($cheques as $cheque) {
                            $cheque->payment_status = $status;
                            $cheque->save();
                            $count++;
                        }
                        return back()->with('success', $count . ' Cheques updated successfully');
                    }
                }

                return back()->with('warning', 'Unknown operation type.');
            });

        } catch (Exception $e) {
            Log::error('Error in Bulk Update', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }
}
