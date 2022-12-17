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
Route::get('customers/user/{user}', [CustomerController::class, 'showByUser']); // -> Get Customer From User
Route::get('customers/{month}/this_month', [CustomerController::class, 'get_number_customers_created_this_month']);


/* --- [API Routes] -> Users --- */
Route::resource('users', UserController::class);
Route::patch('users/block/{id}', [UserController::class, 'toogle']); // -> Block / Unblock User
Route::get('user', [UserController::class, 'show_me'])->middleware('auth:api');
Route::patch('users/{user}/change_password', [UserController::class, 'new_password']); // -> Block / Unblock User

/* --- [API Routes] -> Orders --- */
Route::resource('orders', OrderController::class);
Route::patch('orders/{order}/status', [OrderController::class, 'status']); // -> Change Order Status
Route::get('users/{id}/orders', [OrderController::class, 'get_orders_user']);
Route::get('orders/{year}/numbers', [OrderController::class, 'get_number_orders_by_month']);
Route::get('orders/{month}/revenue', [OrderController::class, 'get_revenue_orders']);
Route::get('orders/{month}/this_month', [OrderController::class, 'get_number_orders_this_month']);


/* --- [API Routes] -> Products --- */
Route::resource('products', ProductController::class);//->middleware('auth:api');



/* --- [API Routes] -> Order Items --- */
Route::patch('order-items/{order_item}/status', [OrderItemController::class, 'status']); // -> Change Order Item Status
Route::get('users/{user}/order-items', [OrderItemController::class, 'get_order_items_by_chef']); // -> Get Order Item from Chef

/* --- [API Routes] -> Auth --- */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
