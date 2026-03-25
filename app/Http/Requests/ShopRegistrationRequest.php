<?php

namespace App\Http\Requests;

use App\Rules\ValidEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ShopRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'owner_name' => 'required|min:3|max:200',
            'shop_name' => 'required|min:3|max:200',
            'owner_email' => [
                'required',
                new ValidEmail,
                'max:200',
                'unique:users,email'
            ],
            'password' => 'required|min:6|max:200',
            'password_confirmation' => 'required|min:6|max:200|same:password'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The shop name is required.',
            'email.required' => "The owner's email is required",
            'shop_email.required' => "The shop email is required",
        ];
    }

    public function passedValidation()
    {
        $this->merge([
            'password' => Hash::make($this->password)
        ]);
    }
}
