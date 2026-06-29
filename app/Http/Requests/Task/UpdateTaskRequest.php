<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority'    => ['nullable', 'in:' . implode(',', Task::PRIORITIES)],
            'status'      => ['nullable', 'in:' . implode(',', Task::STATUSES)],
            'due_date'    => ['nullable', 'date'],
            'reminder_at' => ['nullable', 'date'],
        ];
    }
}
