<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;

class ActivityService
{
    /**
     * Log a generic activity.
     */
    public function log(
        User $user,
        string $type,
        string $description,
        ?Lead $lead = null,
        ?Customer $customer = null,
        array $properties = []
    ): Activity {
        return Activity::create([
            'user_id'     => $user->id,
            'lead_id'     => $lead?->id,
            'customer_id' => $customer?->id,
            'type'        => $type,
            'description' => $description,
            'properties'  => $properties ?: null,
        ]);
    }

    /**
     * Log an activity derived from a Task.
     */
    public function logFromTask(User $user, Task $task, string $type): Activity
    {
        $description = match ($type) {
            Activity::TYPE_TASK_CREATED   => "Task '{$task->title}' was created.",
            Activity::TYPE_TASK_COMPLETED => "Task '{$task->title}' was completed.",
            default                        => "Task '{$task->title}' updated.",
        };

        return $this->log(
            user: $user,
            type: $type,
            description: $description,
            lead: $task->lead,
            customer: $task->customer,
            properties: ['task_id' => $task->id, 'task_title' => $task->title]
        );
    }
}
