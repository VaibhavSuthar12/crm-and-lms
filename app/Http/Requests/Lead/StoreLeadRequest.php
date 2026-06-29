<?php

namespace App\Http\Requests\Lead;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'company'     => ['nullable', 'string', 'max:255'],
            'source'      => ['nullable', 'string', 'max:100'],
            'status'      => ['nullable', 'in:' . implode(',', Lead::STATUSES)],
            'value'       => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }
}
