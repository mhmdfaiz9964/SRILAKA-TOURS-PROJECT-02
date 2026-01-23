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
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $dueReminders = \App\Models\Reminder::with('cheque')
                    ->where('is_read', false)
                    ->orderBy('reminder_date', 'asc')
                    ->get();
                $view->with('dueReminders', $dueReminders);
                $view->with('reminderCount', $dueReminders->count());
            }
        });
    }
}
