<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'photo_url' => $this->photo_url,
            'price' => $this->price,
            'custom' => $this->custom
        ];
    }

    public function with($request)
    {
        return [
            'version' => '1.0.0',
            'api_url' => url('http://fastuga.com'),
            'product_url' => url('http://fastuga.com/api/products/' . $this->id)
        ];
    }
}
