<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::resource('banks', App\Http\Controllers\BankController::class);
    Route::get('/payment-cheques', [App\Http\Controllers\ChequeController::class, 'paymentCheques'])->name('cheques.payment');
    Route::get('/paid-cheques', [App\Http\Controllers\ChequeController::class, 'paidCheques'])->name('cheques.paid');
    Route::get('/cheques/export', [App\Http\Controllers\ChequeController::class, 'export'])->name('cheques.export');
    Route::post('/cheques/{cheque}/reminder', [App\Http\Controllers\ChequeController::class, 'storeReminder'])->name('cheques.reminder');
    Route::post('/reminders/{reminder}/complete', [App\Http\Controllers\ChequeController::class, 'completeReminder'])->name('reminders.complete');
    Route::resource('cheques', App\Http\Controllers\ChequeController::class);
    
    Route::get('/investors/export', [App\Http\Controllers\InvestorController::class, 'export'])->name('investors.export');
    Route::resource('investors', App\Http\Controllers\InvestorController::class);

    Route::resource('in-cheques', App\Http\Controllers\InChequeController::class);
    Route::post('/third-parties', [App\Http\Controllers\ThirdPartyController::class, 'store'])->name('third-parties.store');
    Route::resource('out-cheques', App\Http\Controllers\OutChequeController::class);
    Route::resource('third-party-cheques', App\Http\Controllers\ThirdPartyChequeController::class);
    Route::post('/cheques/{cheque}/add-payment', [App\Http\Controllers\ChequeController::class, 'addPayment'])->name('cheques.add-payment');
    Route::post('/cheques/{cheque}/update-third-party', [App\Http\Controllers\ChequeController::class, 'updateThirdPartyStatus'])->name('cheques.update-third-party');
    Route::post('/cheques/bulk-update', [App\Http\Controllers\ChequeBulkController::class, 'updateBulkStatus'])->name('cheques.bulk-update');
    
    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::resource('customers', App\Http\Controllers\CustomerController::class);
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::resource('sales', App\Http\Controllers\SaleController::class);
    Route::post('/sales/{sale}/add-payment', [App\Http\Controllers\SaleController::class, 'addPayment'])->name('sales.add-payment');
    Route::resource('purchases', App\Http\Controllers\PurchaseController::class);
    
    Route::post('/cheque-reasons', [App\Http\Controllers\ChequeReasonController::class, 'store'])->name('cheque-reasons.store');
    Route::post('/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/system', [App\Http\Controllers\SystemController::class, 'index'])->name('system.index');
    Route::post('/system/update', [App\Http\Controllers\SystemController::class, 'update'])->name('system.update');
    Route::post('/system/storage-link', [App\Http\Controllers\SystemController::class, 'linkStorage'])->name('system.storage-link');
    
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
});
