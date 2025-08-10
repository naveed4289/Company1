<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'email'      => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[a-z0-9._%+-]+@gmail\.com$/'
            ],
            'password'   => [
                'required',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => 'Correct Email format only lowercase  @gmail.com .',
            'password.regex' => 'Password: min 6 chars, 1 uppercase, 1 lowercase, 1 number aur 1 special character required.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
