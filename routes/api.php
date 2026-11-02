<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

// login
Route::post('login', [ApiController::class, 'login']);

// need insert token to use
Route::middleware('auth:sanctum')->group(function () {
    // Get all products
    Route::get('products', [ApiController::class, 'product_list']);
    // Get single product by id
    Route::get('products/{id}', [ApiController::class, 'get_product']);
    // Create new product
    Route::post('products', [ApiController::class, 'store_product']);
    // Update product
    Route::put('products/{id}', [ApiController::class, 'update_product']);
    // Delete product
    Route::delete('products/{id}', [ApiController::class, 'destroy_product']);
    

    // get categories and suppliers
    Route::get('categories', [ApiController::class, 'get_categories']);
    Route::get('suppliers', [ApiController::class, 'get_suppliers']);

    // Optional: Logout (Revoke current token)
    Route::post('logout', [ApiController::class, 'logout']);
});