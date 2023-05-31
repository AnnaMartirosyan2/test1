<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::namespace('Product')->prefix('products')->group(function() {
    Route::controller(ProductInfoController::class)->group(function() {
       Route::get('/', 'index');
       Route::get('/{product}', 'show');
    });
});
