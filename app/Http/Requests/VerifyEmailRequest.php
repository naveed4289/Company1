<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:users,id',
            'hash' => 'required|string',
        ];
    }

    public function prepareForValidation()
    {
        // Add route parameters to validation
        $this->merge([
            'id' => $this->route('id'),
            'hash' => $this->route('hash')
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Invalid verification data'
            ], 403)
        );
    }
}
