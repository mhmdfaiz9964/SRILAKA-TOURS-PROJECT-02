<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\InChequeController;
use App\Http\Controllers\Api\OutChequeController;
use App\Http\Controllers\Api\ThirdPartyChequeController;
use App\Http\Controllers\Api\ChequeController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CategoryController;

// ─── Public ─────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Authenticated (Sanctum) ────────────────────────────
Route::middleware('auth:sanctum')->as('api.')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Products
    Route::apiResource('products', ProductController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Customers
    Route::apiResource('customers', CustomerController::class);
    Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger']);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('/suppliers/{supplier}/ledger', [SupplierController::class, 'ledger']);

    // Investors
    Route::apiResource('investors', InvestorController::class);

    // Sales
    Route::apiResource('sales', SaleController::class);
    Route::post('/sales/{sale}/add-payment', [SaleController::class, 'addPayment']);
    Route::get('/sales/{sale}/fetch-data', [SaleController::class, 'fetchSaleData']);

    // Sales Return
    Route::get('/sales-returns', [SaleController::class, 'returnIndex']);
    Route::get('/sales-returns/create', [SaleController::class, 'returnFormData']);
    Route::post('/sales-returns', [SaleController::class, 'storeReturn']);

    // Purchases
    Route::apiResource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/add-payment', [PurchaseController::class, 'addPayment']);

    // In Cheques
    Route::apiResource('in-cheques', InChequeController::class);

    // Out Cheques
    Route::apiResource('out-cheques', OutChequeController::class);

    // Third Party Cheques
    Route::apiResource('third-party-cheques', ThirdPartyChequeController::class);

    // RTN Cheques (Returned/Bounced)
    Route::apiResource('cheques', ChequeController::class);
    Route::post('/cheques/{cheque}/add-payment', [ChequeController::class, 'addPayment']);
    Route::post('/cheques/{cheque}/reminder', [ChequeController::class, 'storeReminder']);
    Route::post('/reminders/{reminder}/complete', [ChequeController::class, 'completeReminder']);
    Route::get('/paid-cheques', [ChequeController::class, 'paidCheques']);
    Route::match(['get', 'post', 'put', 'patch'], '/cheque-bulk-action', [ChequeController::class, 'bulkUpdate']);

    // Banks
    Route::apiResource('banks', BankController::class);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::post('/expense-categories', [ExpenseController::class, 'storeCategory']);
    Route::get('/expense-categories', [ExpenseController::class, 'getCategories']);

    // Reports - Daily Ledger
    Route::get('/reports/daily-ledger', [ReportController::class, 'dailyLedger']);
    Route::post('/reports/daily-ledger/update', [ReportController::class, 'updateDailyLedger']);
    Route::get('/reports/daily-ledger/details/{date}', [ReportController::class, 'getDailyLedgerDetails']);
    Route::delete('/reports/daily-ledger/{id}', [ReportController::class, 'destroyDailyLedgerEntry']);

    // Reports - Balance Sheet
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet']);
    Route::post('/reports/balance-sheet/update', [ReportController::class, 'updateBalanceSheet']);
    Route::get('/reports/balance-sheet/details/{date}', [ReportController::class, 'getBalanceSheetDetails']);
    Route::delete('/reports/balance-sheet/{id}', [ReportController::class, 'destroyBalanceSheet']);

    // Lookup endpoints for dropdowns
    Route::get('/lookup/customers', [CustomerController::class, 'lookup']);
    Route::get('/lookup/suppliers', [SupplierController::class, 'lookup']);
    Route::get('/lookup/products', [ProductController::class, 'lookup']);
    Route::get('/lookup/banks', [BankController::class, 'lookup']);
    Route::get('/lookup/categories', [CategoryController::class, 'lookup']);
});
