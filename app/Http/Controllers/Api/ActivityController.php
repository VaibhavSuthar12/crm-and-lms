<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Global activity feed.
     */
    public function index(Request $request): JsonResponse
    {
        $activities = Activity::with(['user:id,name', 'lead:id,title', 'customer:id,first_name,last_name'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json($activities);
    }

    /**
     * Timeline for a specific lead.
     */
    public function leadTimeline(Lead $lead): JsonResponse
    {
        $activities = $lead->activities()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json(['activities' => $activities]);
    }

    /**
     * Timeline for a specific customer.
     */
    public function customerTimeline(Customer $customer): JsonResponse
    {
        $activities = $customer->activities()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json(['activities' => $activities]);
    }
}
