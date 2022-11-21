<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateNewProduct extends FormRequest
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
            'name' => 'required|unique:posts|max:255',
            'type' => 'required|in:hot dish,cold dish,drink,dessert',
            'description' => 'required|max:255',
            'photo_url' => 'required|max:255',
            'price' => 'required|regex:/^\d{1,8}+(\.\d{1,2})?$/"',
        ];
    }
    public function messages(){
        return[
            ''
        ];
    }
}
