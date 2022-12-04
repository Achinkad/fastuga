<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('type')) {
            $this->merge(['type' => strtolower($this->type)]);
        }
    }

    public function rules()
    {
        return [
            'name' => ['required', 'min:3', 'max:255', Rule::unique('products')->ignore($this->product)],
            'type' => 'required|in:hot dish,cold dish,drink,dessert',
            'description' => 'required|max:255',
            'photo_url' => 'sometimes',
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
