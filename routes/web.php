<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PosController::class, 'index'])->name('pos');
Route::post('/sales', [PosController::class, 'store'])->name('sales.store');
Route::get('/sales/stats', [PosController::class, 'stats'])->name('sales.stats');
Route::get('/orders', [SaleController::class, 'index'])->name('orders.index');
Route::get('/orders/{sale}', [SaleController::class, 'show'])->name('orders.show');
Route::resource('products', ProductController::class)->except('show');
Route::get('/sales-report', [ReportController::class, 'index'])->name('reports.sales');
