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

    protected function prepareForValidation()
    {
        if ($this->has('payment_type')) {
            $this->merge(['payment_type' => strtoupper($this->payment_type)]);
        }

        if ($this->has('items')) {
            $items = array();
            foreach ($this->request->all()['items'] as $item) { array_push($items, json_decode($item)); }
            $this->merge(['items' => $items]);
        }
    }

    public function rules()
    {
        $rules = [
            'ticket_number' => 'sometimes|min:1|max:99',
            'status' => 'sometimes|in:P,R,D,C',
            'customer_id' => 'nullable',
            'total_price' => 'required',
            'total_paid' => 'required',
            'total_paid_with_points' => 'required',
            'points_gained' => 'required',
            'points_used_to_pay' => 'required',
            'payment_type' => 'nullable|in:VISA,PAYPAL,MBWAY',
            'date' => 'required|date',
            'delivered_by' => 'nullable',
            'custom' => 'nullable',
            'items' => 'present|array',
            'items.*.order_id' => 'sometimes|exists:orders,id',
            'items.*.order_local_number' => 'sometimes',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.status' => 'sometimes|in:W,P,R',
            'items.*.price' => 'sometimes',
            'items.*.preparation_by' => 'nullable',
            'items.*.notes' => 'nullable',
            'items.*.custom' => 'nullable'
        ];

        switch ($this->request->get('payment_type')) {
            case 'VISA':
                $reference_rule = ['payment_reference' => ['required', 'regex:/^[1-9]\d{15}$/']];
                break;

            case 'PAYPAL':
                $reference_rule = ['payment_reference' => 'required|email'];
                break;

            case 'MBWAY':
                $reference_rule = ['payment_reference' => ['required', 'regex:/^[1-9]\d{8}$/']];
                break;

            default:
                $reference_rule = ['payment_reference' => 'nullable'];
                break;
        }

        return array_merge($rules, $reference_rule);
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'data' => $validator->errors()
        ], 400));
    }
}
