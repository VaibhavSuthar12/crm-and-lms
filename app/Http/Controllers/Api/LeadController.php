<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Models\Activity;
use App\Models\Lead;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private ActivityService $activityService) {}

    /**
     * List leads with search & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['assignee:id,name,email', 'creator:id,name']);

        // Role-based filtering: Sales Executive sees only their leads
        if ($request->user()->hasRole('Sales Executive')) {
            $query->assignedTo($request->user()->id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        $leads = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    /**
     * Create a new lead.
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $this->activityService->log(
            user: $request->user(),
            type: Activity::TYPE_LEAD_CREATED,
            description: "Lead '{$lead->title}' was created.",
            lead: $lead,
            properties: ['lead_title' => $lead->title]
        );

        return response()->json([
            'message' => 'Lead created successfully.',
            'lead'    => $lead->load('assignee:id,name', 'creator:id,name'),
        ], 201);
    }

    /**
     * Show a single lead.
     */
    public function show(Lead $lead): JsonResponse
    {
        return response()->json([
            'lead' => $lead->load([
                'assignee:id,name,email',
                'creator:id,name',
                'tasks',
                'activities.user:id,name',
                'customer',
            ]),
        ]);
    }

    /**
     * Update a lead.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $oldStatus = $lead->status;
        $oldAssignee = $lead->assigned_to;

        $lead->update($request->validated());

        // Log status change
        if ($oldStatus !== $lead->status) {
            $this->activityService->log(
                user: $request->user(),
                type: Activity::TYPE_STATUS_CHANGED,
                description: "Lead status changed from '{$oldStatus}' to '{$lead->status}'.",
                lead: $lead,
                properties: ['from' => $oldStatus, 'to' => $lead->status]
            );
        }

        // Log assignment change
        if ($oldAssignee !== $lead->assigned_to) {
            $this->activityService->log(
                user: $request->user(),
                type: Activity::TYPE_LEAD_ASSIGNED,
                description: "Lead was reassigned.",
                lead: $lead,
                properties: ['assigned_to' => $lead->assigned_to]
            );
        }

        return response()->json([
            'message' => 'Lead updated successfully.',
            'lead'    => $lead->load('assignee:id,name', 'creator:id,name'),
        ]);
    }

    /**
     * Delete a lead (soft delete).
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully.']);
    }

    /**
     * Assign lead to a user.
     */
    public function assign(Request $request, Lead $lead): JsonResponse
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $lead->update(['assigned_to' => $request->assigned_to]);

        $this->activityService->log(
            user: $request->user(),
            type: Activity::TYPE_LEAD_ASSIGNED,
            description: "Lead '{$lead->title}' was assigned.",
            lead: $lead,
            properties: ['assigned_to' => $request->assigned_to]
        );

        return response()->json([
            'message' => 'Lead assigned successfully.',
            'lead'    => $lead->load('assignee:id,name'),
        ]);
    }

    /**
     * Update lead status.
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Lead::STATUSES),
        ]);

        $oldStatus = $lead->status;
        $lead->update(['status' => $request->status]);

        $this->activityService->log(
            user: $request->user(),
            type: Activity::TYPE_STATUS_CHANGED,
            description: "Lead status changed from '{$oldStatus}' to '{$lead->status}'.",
            lead: $lead,
            properties: ['from' => $oldStatus, 'to' => $lead->status]
        );

        return response()->json([
            'message' => 'Status updated.',
            'lead'    => $lead,
        ]);
    }
}
