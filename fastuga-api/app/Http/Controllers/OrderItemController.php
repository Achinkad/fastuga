<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use App\Models\User;

class OrderItemController extends Controller
{
    public function store($item, $order_id) // -> Stores Order Items for an Order
    {
       
        $order_item = new OrderItem;
        $order_item->fill($item);
      
        $order_item->order_id = $order_id;

        /* --- Handle Status --- */
        $order_item->status = $item->product->type == "hot dish" ? "W" : "R";

        /* --- Handle Order Local Number --- */
        $latest_item = OrderItem::select('order_local_number')->latest('id')->where('order_id', $order_item->order_id)->first();
        $order_item->order_local_number = $latest_item ? ++$latest_item->order_local_number : 1;

        /* --- Handle Price --- */
        $order_item->price = $item->product->price;

        $order_item->save();
    }

    public function update($item, $order_item)
    {
        $order_item->fill($item);
        $order_item->save();
    }

    public function status(Request $request, OrderItem $order_item) // -> Change Order Item Status (Request -> Status:W,P,R)
    {
        $request->validate(['status' => 'required|in:W,P,R']);
        $order_item->status = $request->input('status');
        $order_item->save();
        return new OrderItemResource($order_item);
    }

    public function get_order_items_by_chef(User $user) // -> Gets All Order Items From a Chef
    {
        if ($user->type != "EC") { abort(400); }
        $order_items = OrderItem::where('preparation_by', $user->id)->paginate(10);
        return OrderItemResource::collection($order_items);
    }
}
