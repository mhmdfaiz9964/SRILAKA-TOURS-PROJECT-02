<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    public function index()
    {
        $logs = ChangeLog::latest()->get();
        return view('system.index', compact('logs'));
    }

    public function update(Request $request)
    {
        try {
            // Simulate System Update
            // 1. Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // 2. Clear cache
            Artisan::call('optimize:clear');

            // Add to Change Log
            ChangeLog::create([
                'version' => 'v' . (ChangeLog::count() + 1.0),
                'title' => 'System Auto Update',
                'description' => 'System performed an automatic optimization and migration sequence.',
                'type' => 'update'
            ]);

            return redirect()->route('system.index')->with('success', 'System updated successfully! All services are optimized.');
        } catch (\Exception $e) {
            return redirect()->route('system.index')->with('error', 'Update Failed: ' . $e->getMessage());
        }
    }

    public function linkStorage()
    {
        try {
            Artisan::call('storage:link');
            return back()->with('success', 'Storage linked successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Link Failed: ' . $e->getMessage());
        }
    }
}
