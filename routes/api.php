<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('suppliers/{supplier}/ledger', [SupplierController::class, 'ledger']);
Route::post('suppliers/{supplier}/payment', [SupplierController::class, 'makePayment']);

Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger']);
Route::post('customers/{customer}/payment', [CustomerController::class, 'makePayment']);
