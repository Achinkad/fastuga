<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateNewOrder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'ticket_number' => 'required|min:1|max:99',
            'status' => 'required|in:p,r,d,c',
            'customer_id' => 'required',
            'total_price' => 'required|regex:/^\d{1,8}+(\.\d{1,2})?$/"',
            'total_paid' => 'required|regex:/^\d{1,8}+(\.\d{1,2})?$/"',
            'total_paid_with_points' => 'required|regex:/^\d{1,8}+(\.\d{1,2})?$/"',
            'points_gained' => 'required',
            'points_used_to_pay' => 'required',
            'payment_type' => 'required|in:visa,paypal,mbway',
            'date' => 'required|date',
            'delivered_by' => 'required',

        ];
    }
}
