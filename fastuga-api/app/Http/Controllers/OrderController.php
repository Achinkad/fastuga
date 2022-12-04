<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

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
        $order->ticket_number = $latest_ticket >= 99 ? 1 : ++$latest_ticket;
        $order->status = "P";

        /* --- Handle Payment Gateway (Create a New Payment) --- */
        $payment_response = Http::post('https://dad-202223-payments-api.vercel.app/api/payments', [
            "type" => strtolower($order->payment_type),
            "reference" => $order->payment_reference,
            "value" => floatval($order->total_paid)
        ]);

        if ($payment_response->failed()) { return $payment_response->throw(); }

        /* --- Handle Points System --- */
        if ($order->customer_id) {
            $points = intval(round($order->total_paid / 10, 0, PHP_ROUND_HALF_DOWN));
            $order->customer->points += abs($points - $order->points_used_to_pay);
            $order->points_gained = $points;
            $order->customer->save();
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

    public function status(Request $request, Order $order) // -> Change Order Status
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

    public function get_orders_customer(Customer $customer) // -> Get Orders From Customer
    {
        $orders = Order::where('customer_id', $customer->id)->paginate(20);
        return OrderResource::collection($orders);
    }

    // TODO: @anaritaortigoso explain this.. not the same thing as the above one?
    public function get_orders_user($id)
    {
        $orders = Order::where('customer_id', $id)->paginate(20);
        return OrderResource::collection($orders);
    }
}
