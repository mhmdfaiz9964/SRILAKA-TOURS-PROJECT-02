<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'company_logo']); // Exclude file
        
        // Handle text fields
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        // Handle Logo Upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('uploads/settings', 'public');
            Setting::updateOrCreate(['key' => 'company_logo'], ['value' => '/storage/' . $path]);
        }

        // Clear Cache to reflect changes immediately
        \Cache::forget('global_settings');

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
