<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderItemController;




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* --- [API Routes] -> Customers --- */
Route::resource('customers', CustomerController::class);

/* --- [API Routes] -> Users --- */
Route::resource('users', UserController::class);
Route::patch('users/block/{id}', [UserController::class, 'toogle']); // -> Block / Unblock User

/* --- [API Routes] -> Orders --- */
Route::resource('orders', OrderController::class);

/* --- [API Routes] -> Products --- */
Route::resource('products', ProductController::class);

/* --- [API Routes] -> Order Items --- */
Route::resource('order-items', OrderItemController::class);
