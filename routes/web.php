<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/users', function () { return view('users'); })->name('users');
    Route::get('/banks', function () { return view('banks'); })->name('banks');
    Route::get('/cheques', function () { return view('cheques'); })->name('cheques');
});
