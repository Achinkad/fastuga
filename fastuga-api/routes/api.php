<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AuthController;


/* --- [API Routes] -> Customers --- */
Route::resource('customers', CustomerController::class);

/* --- [API Routes] -> Users --- */
Route::resource('users', UserController::class);
Route::patch('users/block/{id}', [UserController::class, 'toogle']); // -> Block / Unblock User
Route::get('user', [UserController::class, 'show_me'])->middleware('auth:api');

/* --- [API Routes] -> Orders --- */
Route::resource('orders', OrderController::class);
Route::patch('orders/status/{order}', [OrderController::class, 'status']); // -> Change Order Status
Route::get('customers/{customer}/orders', [OrderController::class, 'get_orders_customer']); // -> Get Orders From Customer
Route::get('users/{id}/orders', [OrderController::class, 'get_orders_user']); // FIXME: Already exists? Not the same thing as the above one?

/* --- [API Routes] -> Products --- */
Route::resource('products', ProductController::class);

/* --- [API Routes] -> Order Items --- */
Route::resource('order-items', OrderItemController::class);

/* --- [API Routes] -> Auth --- */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');


