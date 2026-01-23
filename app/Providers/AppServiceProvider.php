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
                    ->where('reminder_date', '<=', \Carbon\Carbon::now())
                    ->where('is_read', false)
                    ->get();
                $view->with('dueReminders', $dueReminders);
                $view->with('reminderCount', $dueReminders->count());
            }
        });
    }
}
