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
use App\Http\Controllers\OrderItemController;
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

        $order->date = Carbon::now();
        $order->ticket_number = $latest_ticket >= 99 ? 1 : ++$latest_ticket;
        $order->status = "P";

        /* --- Handle Points System --- */
        if ($order->customer_id) {
            $discount_value = $order->points_used_to_pay / 2;

            if (!$order->points_used_to_pay % 2 && $order->points_used_to_pay !=0) { return response()->json(["msg" => "Invalid number of points!"], 422); }
            if ($discount_value >= $order->total_price) { return response()->json(["msg" => "Points exceed order price!"], 422); }

            $order->total_paid_with_points = $discount_value;
            $order->total_paid = $order->total_price - $discount_value;

            $points = intval(round($order->total_paid / 10, 0, PHP_ROUND_HALF_DOWN));
            $order->customer->points -= abs($order->points_used_to_pay);
            $order->customer->points += $points;
            $order->points_gained = $points;

            $order->customer->save();
        } else {
            $order->total_paid = $order->total_price;
            $order->points_gained = 0;
            $order->total_paid_with_points = 0;
        }

        $order->save();

        /* --- Handle Payment Gateway (Create a New Payment) --- */
        $payment_response = Http::withoutVerifying()->post('https://dad-202223-payments-api.vercel.app/api/payments', [
            "type" => strtolower($order->payment_type),
            "reference" => $order->payment_reference,
            "value" => floatval($order->total_paid)
        ]);

        if ($payment_response->failed()) { return $payment_response->throw(); }

        /* --- Handle Order Items --- */
        foreach ($request->input("items") as $item) {
            (new OrderItemController)->store($item, $order->id);
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

        // TODO: Verify if there is new Order Items

        /* --- Handle Order Items --- */
        foreach ($request->input("items") as $item) {
            (new OrderItemController)->update($item, $order->order_item);
        }

        return new OrderResource($order);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $order = Order::where('id', $id)->firstOrFail();
            if ($order->customer) { $order->customer()->dissociate(); }
            if ($order->delivered_by) { $order->delivered_by_user()->dissociate(); }
            foreach ($order->order_item as $item) { $item->delete(); }
            $order->delete();
            return new OrderResource($order);
        });
    }

    /* --- Custom Routes --- */

    public function status(Request $request, Order $order) // -> Change Order Status (Request -> Status:P,R,D,C)
    {

        $request->validate(['status' => 'sometimes|in:P,R,D,C']);

        if ($request->input('status') == "C" && $order->status != "C" && $order->status != "D"){


            /* --- Handle Payment Gateway (Revoke Points & Refund) --- */
            $payment_response = Http::withoutVerifying()->post('https://dad-202223-payments-api.vercel.app/api/refunds', [
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

            $order->total_paid=0.0;
            $order->status = $request->status;

            $order->save();

        }
        return new OrderResource($order);

    }

    public function get_orders_user($id)
    {
        if(auth()->guard('api')->user()->type == "ED"){
            $orders = Order::where('delivered_by', $id)->paginate(20);
            return OrderResource::collection($orders);
        }

        if(auth()->guard('api')->user()->type == "C"){
            $customer = Customer::where('user_id', $id)->firstOrFail();
            $orders = Order::where('customer_id', $customer->id)->paginate(20);
            return OrderResource::collection($orders);
        }
    }

    public function get_number_orders_by_month($year){
        
        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){
            //$orders_this_year=Order::whereYear('date','=',$year);

            $number_orders_by_month=array(Order::whereYear('date','=',$year)->whereMonth('date','=',1)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',2)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',3)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',4)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',5)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',6)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',7)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',8)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',9)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',10)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',11)->count(),
                                        Order::whereYear('date','=',$year)->whereMonth('date','=',12)->count());
        

        return $number_orders_by_month;
                                          
        }
    }

    public function get_revenue_orders($month){
        
        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){

            $sum_orders_last_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',$month-1)->sum('total_paid');
            $sum_orders_this_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',$month)->sum('total_paid');

            $percent_difference=($sum_orders_this_month-$sum_orders_last_month)/$sum_orders_last_month*100;
            
            return array($sum_orders_this_month,$percent_difference);
                                          
        }
    }

    public function get_number_orders_this_month($month){
        
        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){

            $orders_last_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',$month-1)->count();
            $orders_this_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',$month)->count();

            $percent_difference=($orders_this_month-$orders_last_month)/$orders_last_month*100;
            
            return array($orders_this_month,$percent_difference);
                                          
        }
    }
}
