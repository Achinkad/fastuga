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

use App\Models\Order;
use App\Policies\OrderPolicy;


/* --- [API Routes] -> Customers --- */
Route::get('customers/numbers', [CustomerController::class, 'get_number_customers_created_this_month']);
Route::get('customers/users/{user}', [CustomerController::class, 'show_by_user']); // -> Get Customer From User
Route::resource('customers', CustomerController::class);


/* --- [API Routes] -> Users --- */
Route::patch('users/block/{id}', [UserController::class, 'toogle']); // -> Block / Unblock User
Route::get('user', [UserController::class, 'show_me'])->middleware('auth:api');
Route::patch('users/{user}/change_password', [UserController::class, 'new_password']); // -> Block / Unblock User
Route::resource('users', UserController::class);

/* --- [API Routes] -> Orders --- */
Route::patch('orders/{order}/status', [OrderController::class, 'status']); // -> Change Order Status
Route::get('orders/status', [OrderController::class, 'get_count_order_status']);// -> Change Order Status

Route::get('users/{id}/orders', [OrderController::class, 'get_orders_user']);
Route::get('orders/numbers', [OrderController::class, 'get_number_orders_by_month']);
Route::get('orders/revenue', [OrderController::class, 'get_revenue_orders']);
Route::get('orders/this_month', [OrderController::class, 'get_number_orders_this_month']);
Route::resource('orders',OrderController::class);

/* --- [API Routes] -> Products --- */
Route::get('products/best-selling', [ProductController::class, 'get_best_selling_product']);
Route::resource('products', ProductController::class);

/* --- [API Routes] -> Order Items --- */
Route::patch('order-items/{order_item}/status', [OrderItemController::class, 'status']); // -> Change Order Item Status
Route::get('order-items/waiting', [OrderItemController::class, 'get_order_items_waiting']);
Route::get('users/{id}/order-items', [OrderItemController::class, 'get_order_items_by_chef']); // -> Get Order Item from Chef
Route::get('users/{user}/order-items/preparing', [OrderItemController::class, 'get_order_items_by_chef_preparing']);

/* --- [API Routes] -> Auth --- */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
