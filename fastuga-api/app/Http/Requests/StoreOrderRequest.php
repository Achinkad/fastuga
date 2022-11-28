<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ticket_number' => 'sometimes|min:1|max:99',
            'status' => 'sometimes|in:P,R,D,C',
            'customer_id' => 'nullable',
            'total_price' => 'required',
            'total_paid' => 'required',
            'total_paid_with_points' => 'required',
            'points_gained' => 'required',
            'points_used_to_pay' => 'required',
            'payment_type' => 'nullable|in:VISA,PAYPAL,MBWAY',
            'payment_reference' => 'nullable',
            'date' => 'required|date',
            'delivered_by' => 'nullable',
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
