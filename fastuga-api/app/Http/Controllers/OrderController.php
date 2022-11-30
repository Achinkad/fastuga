<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\User;

use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        
        $status=$request->query('status','');
        $qry=Order::query();
        if($status!="-1"){

            $qry->where('status',$status);
            return $qry->paginate(30);
        }
        else{
            return $qry->paginate(30);
        }
     
       
    }

    public function getTasksOfUser(Request $request, Order $order)
    {
        //TaskResource::$format = 'detailed';
        if (!$request->has('include_assigned')) {
            return OrderResource::collection($user->orders->sortByDesc('id'));
        } else {
            return OrderResource::collection($user->orders->merge($user->assigedOrders)->sortByDesc('id'));
        }
    }

    public function store(StoreOrderRequest $request)
    {
        $latest_ticket = Order::select('ticket_number')->latest('id')->whereDate('created_at', Carbon::today())->first()->ticket_number;

        $order = Order::create($request->validated());
        $order->ticket_number = $latest_ticket < 99 ? 1 : ++$latest_ticket;
        $order->status = "P";

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
        return new OrderRequest($order);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $order = Order::where('id', $id)->firstOrFail();
            if ($order->customer) { $user->customer->detatch(); }
            if ($order->delivered_by) { $user->delivered_by->detatch(); }
            return $order->delete();
        });
    }
}
