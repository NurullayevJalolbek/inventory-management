<?php

use App\Http\Controllers\ProductionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
 * Request body:
 * {
   "products":[

    {"id":1, "qty":30},
    {"id":2, "qty":20}
   ]
}
 */

Route::post('/products/produce', [ProductionController::class, 'produce']);


