<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('products.index') : redirect()->route('login');
});


// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// User Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('products', ProductController::class);
    Route::post('/get_product_by_sku', [ProductController::class, 'getProductBySku'])->name('getProductBySku');
    Route::get('/get_products', [ProductController::class, 'getProducts'])->name('getProducts');
    Route::post('/inbound', [ProductController::class, 'inbound']);
    Route::get('/get_product_stock_transaction', [ProductController::class, 'getProductsStockTransaction'])->name('getProductsStockTransaction');
    Route::post('/products/update-stock', [ProductController::class, 'updateStock'])->name('products.updateStock');
});