<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lead_id'     => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority'    => ['nullable', 'in:' . implode(',', Task::PRIORITIES)],
            'status'      => ['nullable', 'in:' . implode(',', Task::STATUSES)],
            'due_date'    => ['nullable', 'date'],
            'reminder_at' => ['nullable', 'date'],
        ];
    }
}
