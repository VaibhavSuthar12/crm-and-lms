<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Activity;
use App\Models\Task;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private ActivityService $activityService) {}

    /**
     * List tasks with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['assignee:id,name', 'lead:id,title', 'customer:id,first_name,last_name']);

        if ($request->user()->hasRole('Sales Executive')) {
            $query->where('assigned_to', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($request->boolean('today')) {
            $query->todayDue();
        }

        return response()->json(
            $query->orderBy('due_date')->paginate($request->get('per_page', 15))
        );
    }

    /**
     * Create a task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $this->activityService->logFromTask($request->user(), $task, Activity::TYPE_TASK_CREATED);

        return response()->json([
            'message' => 'Task created successfully.',
            'task'    => $task->load('assignee:id,name', 'lead:id,title', 'customer:id,first_name,last_name'),
        ], 201);
    }

    /**
     * Show a single task.
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'task' => $task->load('assignee:id,name', 'creator:id,name', 'lead:id,title', 'customer:id,first_name,last_name'),
        ]);
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $wasCompleted = $task->status === Task::STATUS_COMPLETED;
        $task->update($request->validated());

        if (!$wasCompleted && $task->status === Task::STATUS_COMPLETED) {
            $task->update(['completed_at' => now()]);
            $this->activityService->logFromTask($request->user(), $task, Activity::TYPE_TASK_COMPLETED);
        }

        return response()->json([
            'message' => 'Task updated.',
            'task'    => $task->load('assignee:id,name'),
        ]);
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    /**
     * Mark task as complete.
     */
    public function complete(Request $request, Task $task): JsonResponse
    {
        $task->update([
            'status'       => Task::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->activityService->logFromTask($request->user(), $task, Activity::TYPE_TASK_COMPLETED);

        return response()->json(['message' => 'Task marked as complete.', 'task' => $task]);
    }
}
