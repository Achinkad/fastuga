<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreOrderItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('status')) {
            $this->merge(['status' => strtoupper($this->status)]);
        }
    }

    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'order_local_number' => 'sometimes',
            'product_id' => 'required|exists:products,id',
            'status' => 'sometimes|in:W,P,R',
            'price' => 'sometimes',
            'preparation_by' => 'nullable',
            'notes' => 'nullable',
            'custom' => 'nullable'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'data' => $validator->errors()
        ], 400));
    }
}
