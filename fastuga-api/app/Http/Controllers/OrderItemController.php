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

    // TODO: Assing Order Item to a Chef (Hot Dishes) -> Notificate a Chef (WebSockets)
    public function store(StoreOrderItemRequest $request)
    {
        $order_item = new OrderItem;
        $order_item->fill($request->validated());

        /* --- Handle Status --- */
        $order_item->status = $order_item->product->type == "hot dish" ? "W" : "R";

        /* --- Handle Order Local Number --- */
        $latest_item = OrderItem::select('order_local_number')->latest('id')->where('order_id', $order_item->order_id)->first();
        $order_item->order_local_number = $latest_item ? ++$latest_item->order_local_number : 1;

        /* --- Handle Price --- */
        $order_item->price = $order_item->product->price;

        $order_item->save();
        return new OrderItemResource($order_item);
    }

    public function show(OrderItem $order_item)
    {
        return new OrderItemResource($order_item);
    }

    public function update(StoreOrderItemRequest $request, OrderItem $order_item)
    {
        $order_item->fill($request->validated());
        $order_item->save();
        return new OrderItemResource($order_item);
    }

    public function destroy($id)
    {
        return OrderItem::where(['id' => $id])->firstOrFail()->delete();
    }

    /* --- Custom Routes --- */

    public function status(Request $request, OrderItem $order_item) // -> Change Order Item Status (Request -> Status:W,P,R)
    {
        $request->validate(['status' => 'required|in:W,P,R']);
        $order_item->status = $request->input('status');
        $order_item->save();
        return new OrderItemResource($order_item);
    }

    public function get_order_items_user($id)
    {
       
        $order_items = OrderItem::where('preparation_by', $id)->paginate(20);
        return OrderItemResource::collection($order_items);
     
    }
}
