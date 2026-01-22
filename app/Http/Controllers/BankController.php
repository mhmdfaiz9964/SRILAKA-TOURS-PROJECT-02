<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banks = \App\Models\Bank::all();
        return view('banks.index', compact('banks'));
    }

    public function create()
    {
        return view('banks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'logo' => 'nullable|image|max:1024',
        ]);

        $data = $request->only('name', 'code');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('banks', 'public');
        }

        \App\Models\Bank::create($data);

        return redirect()->route('banks.index')->with('success', 'Bank created successfully');
    }

    public function edit(\App\Models\Bank $bank)
    {
        return view('banks.edit', compact('bank'));
    }

    public function update(Request $request, \App\Models\Bank $bank)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'logo' => 'nullable|image|max:1024',
        ]);

        $data = $request->only('name', 'code');

        if ($request->hasFile('logo')) {
            // Delete old logo if need be
            $data['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $bank->update($data);

        return redirect()->route('banks.index')->with('success', 'Bank updated successfully');
    }

    public function destroy(\App\Models\Bank $bank)
    {
        $bank->delete();
        return redirect()->route('banks.index')->with('success', 'Bank deleted successfully');
    }
}
