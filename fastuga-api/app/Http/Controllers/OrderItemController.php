<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderItemResource;
use App\Http\Requests\StoreOrderItemRequest;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
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
