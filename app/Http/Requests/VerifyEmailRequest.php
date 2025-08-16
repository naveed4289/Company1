<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\User;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'   => 'required|integer|exists:users,id',
            'hash' => 'required|string',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'id'   => $this->route('id'),
            'hash' => $this->route('hash')
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first() ?: 'Invalid verification data'
            ], 403)
        );
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = User::find($this->id);

            if ($user && !hash_equals(sha1($user->email), $this->hash)) {
                $validator->errors()->add('hash', 'Invalid verification data');
            }
        });
    }
}
