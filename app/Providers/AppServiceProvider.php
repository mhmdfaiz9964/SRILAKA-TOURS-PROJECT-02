<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $dueReminders = \App\Models\Reminder::with('cheque')
                    ->where('is_read', false)
                    ->orderBy('reminder_date', 'asc')
                    ->get();
                $view->with('dueReminders', $dueReminders);
                $view->with('reminderCount', $dueReminders->count());
            }

            // Share settings globally
            if (!\Cache::has('global_settings')) {
                // simple cache or just load per request if small
                $globalSettings = \App\Models\Setting::all()->pluck('value', 'key');
                \Cache::put('global_settings', $globalSettings, 60); // cache for 1 min or similar
            }
            $view->with('globalSettings', \Cache::get('global_settings'));
        });
    }
}
