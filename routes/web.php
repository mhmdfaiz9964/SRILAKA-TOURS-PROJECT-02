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

// Secure Invoice View (Shared via WhatsApp - Signed URL)
Route::get('/sales/{sale}/pdf', [App\Http\Controllers\SaleController::class, 'generatePdf'])
    ->name('sales.pdf')
    ->middleware('signed');

Route::get('/purchases/{purchase}/pdf', [App\Http\Controllers\PurchaseController::class, 'generatePdf'])
    ->name('purchases.pdf')
    ->middleware('signed');

Route::middleware(['auth'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::resource('banks', App\Http\Controllers\BankController::class);
    Route::get('/payment-cheques', [App\Http\Controllers\ChequeController::class, 'paymentCheques'])->name('cheques.payment');
    Route::get('/paid-cheques', [App\Http\Controllers\ChequeController::class, 'paidCheques'])->name('cheques.paid');
    Route::get('/cheques/export', [App\Http\Controllers\ChequeController::class, 'export'])->name('cheques.export');
    Route::post('/cheques/{cheque}/reminder', [App\Http\Controllers\ChequeController::class, 'storeReminder'])->name('cheques.reminder');
    Route::post('/reminders/{reminder}/complete', [App\Http\Controllers\ChequeController::class, 'completeReminder'])->name('reminders.complete');
    Route::match(['get', 'post', 'put', 'patch'], '/cheque-bulk-action', [App\Http\Controllers\ChequeBulkController::class, 'updateBulkStatus'])->name('cheques.bulk-update-combined');

    Route::get('/investors/export', [App\Http\Controllers\InvestorController::class, 'export'])->name('investors.export');
    Route::resource('investors', App\Http\Controllers\InvestorController::class);

    Route::get('in-cheques/export', [App\Http\Controllers\InChequeController::class, 'export'])->name('in-cheques.export');
    Route::resource('in-cheques', App\Http\Controllers\InChequeController::class);

    Route::post('/third-parties', [App\Http\Controllers\ThirdPartyController::class, 'store'])->name('third-parties.store');
    // Removed out-cheques.bulk-update as it's consolidated above


    Route::get('out-cheques/export', [App\Http\Controllers\OutChequeController::class, 'export'])->name('out-cheques.export');
    Route::resource('out-cheques', App\Http\Controllers\OutChequeController::class);

    Route::get('third-party-cheques/export', [App\Http\Controllers\ThirdPartyChequeController::class, 'export'])->name('third-party-cheques.export');
    Route::resource('third-party-cheques', App\Http\Controllers\ThirdPartyChequeController::class);
    Route::post('/cheques/{cheque}/add-payment', [App\Http\Controllers\ChequeController::class, 'addPayment'])->name('cheques.add-payment');
    Route::post('/cheques/{cheque}/update-third-party', [App\Http\Controllers\ChequeController::class, 'updateThirdPartyStatus'])->name('cheques.update-third-party');
    // Route::post('/cheques/bulk-update', ...) moved up

    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::resource('customers', App\Http\Controllers\CustomerController::class);
    Route::get('/customers/{customer}/ledger/export', [App\Http\Controllers\CustomerController::class, 'exportLedger'])->name('customers.ledger.export');
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::get('/suppliers/{supplier}/ledger/export', [App\Http\Controllers\SupplierController::class, 'exportLedger'])->name('suppliers.ledger.export');
    Route::get('/sales/returns', [App\Http\Controllers\SaleController::class, 'returnIndex'])->name('sales.return.index');
    Route::get('/sales/{sale}/return', [App\Http\Controllers\SaleController::class, 'returnForm'])->name('sales.return.create');
    Route::post('/sales/return', [App\Http\Controllers\SaleController::class, 'storeReturn'])->name('sales.return.store');
    Route::resource('sales', App\Http\Controllers\SaleController::class);
    // Route moved outside to support public sharing with signature
    Route::post('/sales/{sale}/add-payment', [App\Http\Controllers\SaleController::class, 'addPayment'])->name('sales.add-payment');
    Route::resource('purchases', App\Http\Controllers\PurchaseController::class);
    Route::post('/purchases/{purchase}/add-payment', [App\Http\Controllers\PurchaseController::class, 'addPayment'])->name('purchases.add-payment');

    Route::post('/cheque-reasons', [App\Http\Controllers\ChequeReasonController::class, 'store'])->name('cheque-reasons.store');
    Route::post('/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/system', [App\Http\Controllers\SystemController::class, 'index'])->name('system.index');
    Route::post('/system/update', [App\Http\Controllers\SystemController::class, 'update'])->name('system.update');
    Route::post('/system/storage-link', [App\Http\Controllers\SystemController::class, 'linkStorage'])->name('system.storage-link');
    Route::post('/system/backup', [App\Http\Controllers\SystemController::class, 'backupDatabase'])->name('system.backup');

    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    Route::post('expenses/categories', [App\Http\Controllers\ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
    Route::resource('expenses', App\Http\Controllers\ExpenseController::class);

    // Manual Reports Routes
    Route::get('/reports/daily-ledger', [App\Http\Controllers\ReportController::class, 'dailyLedger'])->name('reports.daily-ledger');
    Route::post('/reports/daily-ledger/update', [App\Http\Controllers\ReportController::class, 'updateDailyLedger'])->name('reports.daily-ledger.update');
    Route::post('/reports/daily-ledger/store', [App\Http\Controllers\ReportController::class, 'storeDailyLedgerEntry'])->name('reports.daily-ledger.store');
    Route::get('/reports/daily-ledger/edit/{id}', [App\Http\Controllers\ReportController::class, 'editDailyLedgerEntry'])->name('reports.daily-ledger.edit');
    Route::delete('/reports/daily-ledger/delete/{id}', [App\Http\Controllers\ReportController::class, 'destroyDailyLedgerEntry'])->name('reports.daily-ledger.destroy');

    Route::get('/reports/daily-ledger/history', [App\Http\Controllers\ReportController::class, 'dailyLedgerHistory'])->name('reports.daily-ledger.history');
    Route::get('/reports/daily-ledger/details/{date}', [App\Http\Controllers\ReportController::class, 'getDailyLedgerDetails'])->name('reports.daily-ledger.details');

    Route::get('/reports/balance-sheet', [App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('/reports/balance-sheet/history', [App\Http\Controllers\ReportController::class, 'balanceSheetHistory'])->name('reports.balance-sheet.history');
    Route::post('/reports/balance-sheet/update', [App\Http\Controllers\ReportController::class, 'updateBalanceSheet'])->name('reports.balance-sheet.update');
    Route::get('/reports/balance-sheet/details/{date}', [App\Http\Controllers\ReportController::class, 'getBalanceSheetDetails'])->name('reports.balance-sheet.details');
    Route::get('/reports/balance-sheet/edit/{id}', [App\Http\Controllers\ReportController::class, 'editBalanceSheet'])->name('reports.balance-sheet.edit');
    Route::delete('/reports/balance-sheet/delete/{id}', [App\Http\Controllers\ReportController::class, 'destroyBalanceSheet'])->name('reports.balance-sheet.destroy');

    Route::resource('cheques', App\Http\Controllers\ChequeController::class);
});
