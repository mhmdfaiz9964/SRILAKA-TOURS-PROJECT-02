<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChequeReasonController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['reason' => 'required|unique:cheque_reasons']);
        $reason = \App\Models\ChequeReason::create($request->all());

        if ($request->ajax()) {
            return response()->json($reason);
        }

        return back()->with('success', 'Reason added successfully');
    }
}
