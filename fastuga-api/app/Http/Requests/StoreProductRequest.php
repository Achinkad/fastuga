<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|unique:products|min:3|max:255',
            'type' => 'required|in:hot dish,cold dish,drink,dessert',
            'description' => 'required|max:255',
            'photo_url' => 'required',
            'price' => 'required',
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
