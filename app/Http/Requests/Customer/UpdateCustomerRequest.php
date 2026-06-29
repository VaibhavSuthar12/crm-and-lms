<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name'      => ['sometimes', 'string', 'max:100'],
            'last_name'       => ['sometimes', 'string', 'max:100'],
            'email'           => ['nullable', 'email'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'company_name'    => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url'],
            'company_size'    => ['nullable', 'string'],
            'industry'        => ['nullable', 'string'],
            'address'         => ['nullable', 'string'],
            'city'            => ['nullable', 'string'],
            'state'           => ['nullable', 'string'],
            'country'         => ['nullable', 'string'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'assigned_to'     => ['nullable', 'exists:users,id'],
        ];
    }
}
