<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'points' => $this->points,
            'nif' => $this->nif,
            'default_payment_type' => $this->default_payment_type,
            'default_payment_reference' => $this->default_payment_reference,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->user)
        ];
    }

    public function with($request)
    {
        return [
            'version' => '1.0.0',
            'api_url' => url('http://fastuga.com')
        ];
    }
}
