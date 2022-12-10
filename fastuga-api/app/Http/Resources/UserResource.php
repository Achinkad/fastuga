<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'blocked' => $this->blocked ? true : false,
            'photo_url' => $this->photo_url,
            'type' => $this->type,
            'customer' => $this->customer_id ? new CustomerResource($this->customer) : null
        ];
    }

    public function with($request)
    {
        return [
            'version' => '1.0.0',
            'api_url' => url('http://fastuga.com'),
            'user_url' => url('http://fastuga.com/api/users/' . $this->id)
        ];
    }
}
