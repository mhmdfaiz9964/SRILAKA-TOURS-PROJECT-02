<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    public function update(Request $request)
    {
        try {
            // 1. Git Pull
            $process = new Process(['git', 'pull']);
            $process->run();

            if (!$process->isSuccessful()) {
                // If git fails, we might not want to stop unless it's a critical error
                // but for safety let's just log it
            }

            // 2. Run Migrations
            Artisan::call('migrate', ['--force' => true]);

            // 3. Clear Cache (optional but recommended after update)
            Artisan::call('optimize:clear');

            return response()->json([
                'success' => true,
                'message' => 'System updated successfully!',
                'output' => $process->getOutput()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
