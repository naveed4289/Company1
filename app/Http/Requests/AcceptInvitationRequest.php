<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\CompanyInvitation;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string|exists:company_invitations,token',
        ];
    }

    public function prepareForValidation()
    {
        // Add token from route parameter to validation
        $this->merge([
            'token' => $this->route('token')
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Invalid invitation token'
            ], 404)
        );
    }
}
