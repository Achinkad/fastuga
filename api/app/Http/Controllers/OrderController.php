<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Controllers\OrderItemController;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

        $orders = $request->status != 'all' ? Order::where('status', $request->input('status'))->paginate(20) : Order::paginate(20);
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        if (Auth()->guard('api')->user() != null) {
            if (Auth()->guard('api')->user()->type != "C") {
                abort(403);
            }
        }

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

            if (!$order->points_used_to_pay % 2 && $order->points_used_to_pay !=0) {  return response()->json(['success' => false,
                                                                                                                'data' => ['points' => ['Invalid number of points!']]], 422);}
            if ($discount_value >= $order->total_price) { return response()->json(['success' => false,
                                                                                    'data' => ['points_price' => ['Number of points greater than price']]], 422);}

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

        $x = 0;
        foreach($order->order_item as $i) {
            if ($i->status != "R") { $x = 1; break; }
            }

            if ($x == 0) {
                $order->status = "R";
            }

        $order->save();


        return new OrderResource($order);
    }

    public function show(Order $order)
    {
        /* --- Authorization --- */
        if (Auth()->guard('api')->user()!=null && Auth()->guard('api')->user()->type != "EM" &&(Auth()->guard('api')->user()->customer->id!=$order->customer_id) ) { abort(403); }
        return new OrderResource($order);
    }

    public function update(StoreOrderRequest $request, Order $order)
    {
        if (Auth()->guard('api')->user()->type == "ED" || Auth()->guard('api')->user()->type == "EC" ) { abort(403); }

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
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

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
        if (Auth()->guard('api')->user()->type != "EM" && Auth()->guard('api')->user()->type != "EC" && Auth()->guard('api')->user()->type != "ED") { abort(403); }

        $request->validate(['status' => 'sometimes|in:P,R,D,C']);

        if ($request->has('delivered_by') && $request->input('status') == "D") {
            $order->status = "D";
            $order->delivered_by = $request->input('delivered_by');
            $order->save();
        }

        if ($request->has('delivered_by') && $request->input('status') == "R") {
            $order->status = "R";
            $order->delivered_by = $request->input('delivered_by');
            $order->save();
        }

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

    public function get_orders_user(Request $request, $id)
    {
        if (Auth()->guard('api')->user()->type != "ED" && Auth()->guard('api')->user()->type != "C") { abort(403); }

        $request->validate(['status' => 'sometimes|in:all,P,R,D,C']);


        switch (auth()->guard('api')->user()->type) {
            case 'ED':
                if($request->input('status')!='R'){
                    $orders = $request->status != 'all' ? Order::where('delivered_by',$id)->where('status', $request->input('status'))->paginate(20) : Order::where('delivered_by',$id)->whereIn('status',['D','C'])->paginate(20);
                }
                else{
                    $orders= Order::whereNull('delivered_by')->where('status', $request->input('status'))->paginate(20);
                }

                break;

            case 'C':
                $customer = Customer::where('user_id', $id)->firstOrFail();
                $orders = $request->status != 'all' ? Order::where('customer_id', $customer->id)->where('status', $request->input('status'))->paginate(20) : Order::where('customer_id', $customer->id)->paginate(20);
                break;

            default:
                abort(403);
                break;
        }
        return OrderResource::collection($orders);
    }

    public function get_number_orders_by_month(){


        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){

            $number_orders_by_month=array(Order::whereYear('date','=',date('Y'))->whereMonth('date','=',1)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',2)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',3)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',4)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',5)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',6)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',7)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',8)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',9)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',10)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',11)->count(),
                                        Order::whereYear('date','=',date('Y'))->whereMonth('date','=',12)->count());


        return $number_orders_by_month;

        }
    }

    public function get_revenue_orders(){

        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){

            $sum_orders_last_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',date('m')-1)->sum('total_paid');
            $sum_orders_this_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',date('m'))->sum('total_paid');

            $percent_difference=($sum_orders_this_month-$sum_orders_last_month)/$sum_orders_last_month*100;

            return array($sum_orders_this_month,$percent_difference);

        }
    }

    public function get_number_orders_this_month(){

        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){

            $orders_last_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',date('m')-1)->count();
            $orders_this_month=Order::whereYear('date','=',date('Y'))->whereMonth('date','=',date('m'))->count();

            $percent_difference=($orders_this_month-$orders_last_month)/$orders_last_month*100;

            return array($orders_this_month,$percent_difference);

        }
    }
    public function get_count_order_status(Request $request){
        if (Auth()->guard('api')->user()->type != "ED") { abort(403); }

        $request->validate(['status' => 'sometimes|in:P,R,D,C']);
        $count_orders = Order::where('status', $request->input('status'))->count();
        return $count_orders;
    }

}
