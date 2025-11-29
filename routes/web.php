<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/login'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

    Route::prefix('marketer')->name('marketer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MarketerController::class, 'index'])->name('index');
        Route::post('/assignments/{assignment}/confirm', [\App\Http\Controllers\MarketerController::class, 'confirmAssignment'])->name('assignments.confirm');
        Route::post('/invoices', [\App\Http\Controllers\MarketerController::class, 'storeInvoice'])->name('invoices.store');
        Route::post('/returns/store', [\App\Http\Controllers\MarketerController::class, 'storeReturn'])->name('returns.store');
        Route::post('/returns/warehouse', [\App\Http\Controllers\MarketerController::class, 'warehouseReturn'])->name('returns.warehouse');
    });
});
