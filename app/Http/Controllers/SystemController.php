<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:system-manage');
    }
    public function index()
    {
        $logs = ChangeLog::latest()->get();
        return view('system.index', compact('logs'));
    }

    public function update(Request $request)
    {
        try {
            // 1. Run Git Pull to get latest changes
            $gitProcess = new Process(['git', 'pull', 'origin', 'main']);
            // Set working directory to the project root
            $gitProcess->setWorkingDirectory(base_path());
            $gitProcess->run();

            // 2. Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // 3. Clear cache and optimize
            Artisan::call('optimize:clear');

            $gitOutput = $gitProcess->isSuccessful() ? 'Changes pulled successfully.' : 'Git pull skipped or failed (check permissions).';

            // Add to Change Log
            ChangeLog::create([
                'version' => 'v' . (ChangeLog::count() + 1.0),
                'title' => 'System Core Update',
                'description' => 'Performed Git Pull, Database Migrations, and System Optimization. ' . $gitOutput,
                'type' => 'update'
            ]);

            return redirect()->route('system.index')->with('success', 'System update completed! ' . $gitOutput);
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
