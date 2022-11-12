<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    public function index()
    {
        return OrderItemResource::collection(OrderItem::all());
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return OrderItemResource::collection(OrderItem::where('id', $id)->get());
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
