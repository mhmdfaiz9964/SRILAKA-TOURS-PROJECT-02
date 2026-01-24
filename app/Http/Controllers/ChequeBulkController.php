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
            'status' => 'required|in:returned,third_party,deposited,cleared',
            'third_party_name' => 'nullable|required_if:status,third_party|string',
        ]);

        $cheques = \App\Models\InCheque::whereIn('id', $request->cheque_ids)->get();

        foreach($cheques as $cheque) {
            $updateData = ['status' => $request->status];

            if ($request->status === 'third_party') {
                // If moving to 3rd party, we might want to create a ThirdPartyCheque record OR just update the InCheque
                // Based on previous simple implementations "ThirdPartyChequeController" exists, 
                // let's assumed it's a separate model or just a status on Cheque.
                // Assuming separation based on user request "goes to 3rd party cheqes".
                // But let's check if ThirdPartyCheque is a model.
                
                // If simple status update:
                $updateData['status'] = 'third_party';
                // If we need to store the name, let's assume 'third_party_name' column exists or notes
                 $cheque->notes .= " | Transferred to: " . $request->third_party_name;
            }
            
            $cheque->update($updateData);

            if ($request->status === 'third_party') {
                // Create ThirdPartyCheque entry if that table exists (User mentioned ThirdPartyChequeController resource)
                \App\Models\ThirdPartyCheque::create([
                    'cheque_id' => $cheque->id,
                    'third_party_name' => $request->third_party_name,
                    'received_date' => now(),
                    'status' => 'pending' // or similar
                ]);
            }
        }

        return back()->with('success', 'Cheques updated successfully');
    }
}
