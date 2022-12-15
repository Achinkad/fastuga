<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'total_paid' => $this->total_paid,
            'total_paid_with_points' => $this->total_paid_with_points,
            'points_gained' => $this->points_gained,
            'points_used_to_pay' => $this->points_used_to_pay,
            'payment_type' => $this->payment_type,
            'payment_reference' => $this->payment_reference,
            'date' => $this->date,
            'custom' => $this->custom,
            'customer_id' => $this->customer_id,
            'customer' => $this->customer_id ? new CustomerResource($this->customer) : null,
            'delivered_by' => $this->delivered_by,
            'user' => $this->delivered_by ? new UserResource($this->delivered_by_user) : null,
            'order_item' => OrderItemResource::collection($this->order_item)
        ];
    }
}
