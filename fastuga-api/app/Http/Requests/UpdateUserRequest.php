<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user)],
            'type' => 'required|in:C,EC,ED,EM',
            'blocked' => 'required|in:0,1',
            'photo_url' => 'nullable',
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

    public function messages()
    {
        return [
            'email.unique' => 'The e-mail is already registered. Please, try choose another one.'
        ];
    }
}
