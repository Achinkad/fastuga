<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return OrderResource::collection(Order::paginate(30));
    }

    public function store(ValidateNewOrder $request)
    {
        $newOrder = Order::create($request->validated());
        return new TaskResource($newTask);
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(ValidateNewOrder $request, Order $order)
    {
        $order->update($request->validated());
        return new OrderRequest($order);
    }

    public function destroy(Order $order)
    {
        //falta algum detach?
        $order->customer()->detatch();
        $order->delivered_by()->detatch();
        $order->delete();
        return new OrderResource($order);
    }
}
