<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'phone' => 'required|min:0|max:20',
            'points' => 'required|min:0',
            'nif' => 'nullable|digits:9',
            'default_payment_type' => 'nullable|in:VISA,PAYPAL,MBWAY',
            'default_payment_reference' => 'nullable',
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
