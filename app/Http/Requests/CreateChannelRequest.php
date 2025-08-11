<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,private',
            'company_id' => 'required|exists:companies,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Channel name is required',
            'name.max' => 'Channel name cannot exceed 255 characters',
            'type.required' => 'Channel type is required',
            'type.in' => 'Channel type must be either public or private',
            'company_id.required' => 'Company ID is required',
            'company_id.exists' => 'Selected company does not exist'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-assign authenticated user's company if not provided
        if (!$this->has('company_id')) {
            $user = auth()->user();
            if ($user && $user->company) {
                $this->merge([
                    'company_id' => $user->company->id
                ]);
            }
        }
    }
}
