<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return dashboard summary stats.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isSalesExec = $user->hasRole('Sales Executive');

        // Leads query scope
        $leadsQuery = Lead::query();
        if ($isSalesExec) {
            $leadsQuery->where('assigned_to', $user->id);
        }

        // Tasks query scope
        $tasksQuery = Task::query();
        if ($isSalesExec) {
            $tasksQuery->where('assigned_to', $user->id);
        }

        // ---- Counts ----
        $totalLeads     = (clone $leadsQuery)->count();
        $totalCustomers = $isSalesExec
            ? Customer::where('assigned_to', $user->id)->count()
            : Customer::count();

        $todaysFollowups = (clone $tasksQuery)->todayDue()->count();
        $overdueTasks    = (clone $tasksQuery)->overdue()->count();

        // ---- Leads by Status ----
        $leadsByStatus = (clone $leadsQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ---- Conversion Rate ----
        $wonLeads = (clone $leadsQuery)->where('status', Lead::STATUS_WON)->count();
        $conversionRate = $totalLeads > 0
            ? round(($wonLeads / $totalLeads) * 100, 2)
            : 0;

        // ---- Recent Activities ----
        $recentActivities = \App\Models\Activity::with('user:id,name')
            ->when($isSalesExec, fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_leads'       => $totalLeads,
                'total_customers'   => $totalCustomers,
                'todays_followups'  => $todaysFollowups,
                'overdue_tasks'     => $overdueTasks,
                'conversion_rate'   => $conversionRate,
            ],
            'leads_by_status'    => $leadsByStatus,
            'recent_activities'  => $recentActivities,
        ]);
    }
}
