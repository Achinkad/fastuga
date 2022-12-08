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
Route::patch('users/{user}/change_password', [UserController::class, 'new_password']); // -> Block / Unblock User

/* --- [API Routes] -> Orders --- */
Route::resource('orders', OrderController::class);
Route::patch('orders/{order}/status', [OrderController::class, 'status']); // -> Change Order Status
Route::get('users/{id}/orders', [OrderController::class, 'get_orders_user']);

/* --- [API Routes] -> Products --- */
Route::resource('products', ProductController::class);//->middleware('auth:api');

/* --- [API Routes] -> Order Items --- */
Route::patch('order-items/{order_item}/status', [OrderItemController::class, 'status']); // -> Change Order Item Status
Route::get('users/{id}/order-items', [OrderItemController::class, 'get_order_items_user']);
/* --- [API Routes] -> Auth --- */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
