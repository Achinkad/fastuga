<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use App\Models\OrderItem;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->status != 'all' ? Order::where('status', $request->input('status'))->paginate(20) : Order::paginate(20);
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $latest_order = Order::select('ticket_number')->latest('id')->whereDate('created_at', Carbon::today())->first();
        $latest_ticket = $latest_order ? $latest_order->ticket_number : 0;

        $order = new Order;
        $order->fill($request->validated());

        /* --- Handle Payment Gateway (Create a New Payment) --- */
        $payment_response = Http::post('https://dad-202223-payments-api.vercel.app/api/payments', [
            "type" => strtolower($order->payment_type),
            "reference" => $order->payment_reference,
            "value" => floatval($order->total_paid)
        ]);

        if ($payment_response->failed()) { return $payment_response->throw(); }

        /* --- Ticket Number + Status --- */
        $order->ticket_number = $latest_ticket >= 99 ? 1 : ++$latest_ticket;
        $order->status = "P";
        $order->save();

        /* --- Handle Points System --- */
        if ($order->customer_id) {
            $points = intval(round($order->total_paid / 10, 0, PHP_ROUND_HALF_DOWN));
            $order->customer->points += abs($points - $order->points_used_to_pay);
            $order->points_gained = $points;
            $order->customer->save();
        }

        /* --- Handle Order Items --- */
        foreach ($request->input("items") as $order_item) {
            $this->store_order_item($order_item, $order->id);
        }

        $order->save();
        return new OrderResource($order);
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(StoreOrderRequest $request, Order $order)
    {
        $order->fill($request->validated());
        $order->save();
        return new OrderResource($order);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $order = Order::where('id', $id)->firstOrFail();
            if ($order->customer) { $order->customer()->detach(); }
            if ($order->delivered_by) { $order->delivered_by_user()->detach(); }
            return $order->delete();
        });
    }

    /* --- Custom Routes --- */

    public function status(Request $request, Order $order) // -> Change Order Status (Request -> Status:P,R,D,C)
    {
        $request->validate(['status' => 'required|in:P,R,D,C']);

        if ($request->input('status') == "C" && $order->status != "C") {
            /* --- Handle Payment Gateway (Revoke Points & Refund) --- */
            $payment_response = Http::post('https://dad-202223-payments-api.vercel.app/api/refunds', [
                "type" => strtolower($order->payment_type),
                "reference" => $order->payment_reference,
                "value" => floatval($order->total_paid)
            ]);

            if ($payment_response->failed()) { return $payment_response->throw(); }

            /* --- Handle Points System --- */
            if ($order->customer_id) {
                $order->customer->points -= abs($order->points_gained - $order->points_used_to_pay);
                $order->customer->save();
            }
        }

        $order->status = $request->input('status');
        $order->save();
        return new OrderResource($order);
    }

    public function get_orders_user($id)
    {
      
        if(auth()->guard('api')->user()->type == "ED"){

            $orders = Order::where('delivered_by', $id)->paginate(20);
            return OrderResource::collection($orders);
        }

        if(auth()->guard('api')->user()->type == "C"){
            $customer=Customer::where('user_id',$id)->firstOrFail();
           
            $orders = Order::where('customer_id', $customer->id)->paginate(20);
            return OrderResource::collection($orders);
        }
       
    }
       
    

    /* --- Custom Functions --- */

    private function store_order_item($item, $order_id) // -> Stores Order Items for an Order
    {
        $order_item = new OrderItem;
        $order_item->fill($item);

        $order_item->order_id = $order_id;

        /* --- Handle Status --- */
        $order_item->status = $order_item->product->type == "hot dish" ? "W" : "R";

        /* --- Handle Order Local Number --- */
        $latest_item = OrderItem::select('order_local_number')->latest('id')->where('order_id', $order_item->order_id)->first();
        $order_item->order_local_number = $latest_item ? ++$latest_item->order_local_number : 1;

        /* --- Handle Price --- */
        $order_item->price = $order_item->product->price;

        $order_item->save();
    }
}
