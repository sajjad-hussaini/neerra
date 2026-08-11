<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/shop', [StorefrontController::class, 'index'])->name('shop');
Route::post('/shop/orders', [StorefrontController::class, 'store'])->name('shop.orders.store');

Route::middleware('auth')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos');
    Route::post('/sales', [PosController::class, 'store'])->name('sales.store');
    Route::get('/sales/stats', [PosController::class, 'stats'])->name('sales.stats');
    Route::get('/orders', [SaleController::class, 'index'])->name('orders.index');
    Route::get('/orders/{sale}', [SaleController::class, 'show'])->name('orders.show');
    Route::resource('products', ProductController::class)->except('show');
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/sales-report', [ReportController::class, 'index'])->name('reports.sales');

    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/ledger', [SupplierController::class, 'ledger']);
    Route::post('suppliers/{supplier}/payment', [SupplierController::class, 'makePayment']);

    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger']);
    Route::post('customers/{customer}/payment', [CustomerController::class, 'makePayment']);
});
