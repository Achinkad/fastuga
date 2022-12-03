<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderItemResource;
use App\Http\Requests\StoreOrderItemRequest;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    public function index()
    {
        return OrderItemResource::collection(OrderItem::paginate(20));
    }

    public function store(StoreOrderItemRequest $request)
    {
        $order_item = new OrderItem;
        $order_item->fill($request->validated());
        $order_item->status = $order_item->product->type == "hot dish" ? "W" : "R";

        // TODO: Assing Order Item to a Chef (Hot Dishes) -> Notificate a Chef (WebSockets)
        // TODO: Assing a Ticket Order Local

        $order_item->save();
        return new OrderItemResource($order_item);
    }

    public function show(OrderItem $order_item)
    {
        return new OrderItemResource($order_item);
    }

    public function update(StoreOrderItemRequest $request, OrderItem $order_item)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
