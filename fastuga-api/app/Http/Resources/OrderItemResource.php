<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'price' => $this->price,
            'notes' => $this->notes,
            'custom' => $this->custom,
            'order_id' => $this->order_id,
            'order_local_number' => $this->order_local_number,
            'product_id' => $this->product_id,
            'preparation_by' => $this->preparation_by
        ];
    }
}
