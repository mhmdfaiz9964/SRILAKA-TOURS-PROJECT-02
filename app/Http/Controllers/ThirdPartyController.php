<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThirdPartyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:third_parties,name']);
        
        $tp = \App\Models\ThirdParty::create($request->all());

        if ($request->ajax()) {
            return response()->json(['success' => true, 'third_party' => $tp]);
        }
        return back()->with('success', 'Third Party created');
    }
}
