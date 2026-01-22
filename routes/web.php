<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('banks', App\Http\Controllers\BankController::class);
    Route::resource('cheques', App\Http\Controllers\ChequeController::class);
    
    Route::post('/cheque-reasons', [App\Http\Controllers\ChequeReasonController::class, 'store'])->name('cheque-reasons.store');
    Route::post('/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
});
