<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AgentPerformanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!auth()->user()->is_admin && !auth()->user()->is_manager) {
                    return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
                }
                return $next($request);
            }),
        ];
    }

    public function index(Request $request)
    {
        $query = User::whereNotNull('department_id')->orWhereHas('departments');

        if ($request->has('department_id') && $request->department_id !== 'all') {
            $query->whereHas('departments', function($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        if ($request->has('topic_id') && $request->topic_id !== 'all') {
            $query->whereHas('topics', function($q) use ($request) {
                $q->where('topics.id', $request->topic_id);
            });
        }

        $agents = $query->with(['departments', 'topics'])->get()->map(function ($agent) {
            // Base tickets query for this agent
            $assignedTickets = \App\Models\Ticket::where('current_assignee_user_id', $agent->id);
            
            // Metrics
            $openTickets = (clone $assignedTickets)->whereNotIn('status', ['Resolved', 'Closed'])->count();
            $resolvedTickets = (clone $assignedTickets)->whereIn('status', ['Resolved', 'Closed'])->count();
            
            // Completed by Urgency
            $resolvedByUrgency = [
                'Urgent' => (clone $assignedTickets)->whereIn('status', ['Resolved', 'Closed'])->where('priority', 'Urgent')->count(),
                'High' => (clone $assignedTickets)->whereIn('status', ['Resolved', 'Closed'])->where('priority', 'High')->count(),
                'Medium' => (clone $assignedTickets)->whereIn('status', ['Resolved', 'Closed'])->where('priority', 'Medium')->count(),
                'Low' => (clone $assignedTickets)->whereIn('status', ['Resolved', 'Closed'])->where('priority', 'Low')->count(),
            ];

            // Backlog Weight
            $backlogWeight = (clone $assignedTickets)->whereNotIn('status', ['Resolved', 'Closed'])->sum('weight');

            // Transfers Out
            $transfersOutCount = \App\Models\TicketTransfer::where('from_user_id', $agent->id)->count();

            // Efficiency (resolved tickets where resolved_at <= sla_breach_at)
            $totalResolvedWithSla = (clone $assignedTickets)
                ->whereIn('status', ['Resolved', 'Closed'])
                ->whereNotNull('sla_breach_at')
                ->whereNotNull('resolved_at')
                ->count();
            
            $metSlaCount = (clone $assignedTickets)
                ->whereIn('status', ['Resolved', 'Closed'])
                ->whereNotNull('sla_breach_at')
                ->whereNotNull('resolved_at')
                ->whereColumn('resolved_at', '<=', 'sla_breach_at')
                ->count();

            $efficiencyPercentage = $totalResolvedWithSla > 0 
                ? round(($metSlaCount / $totalResolvedWithSla) * 100, 1) 
                : 100;

            // Burndown Rate (tickets resolved in the last 7 days vs tickets assigned)
            $last7Days = now()->subDays(7);
            
            // Get resolved counts grouped by date for the last 7 days
            $resolvedByDayQuery = (clone $assignedTickets)
                ->whereIn('status', ['Resolved', 'Closed'])
                ->where('resolved_at', '>=', $last7Days->startOfDay())
                ->select(DB::raw('DATE(resolved_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date');

            $resolvedLastWeek = $resolvedByDayQuery->sum();

            // Format into an array of the last 7 days (e.g. [0, 2, 0, 5, ...])
            $burndownData = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateString = now()->subDays($i)->format('Y-m-d');
                $burndownData[] = $resolvedByDayQuery->get($dateString, 0);
            }
            
            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'departments' => $agent->departments->pluck('name'),
                'topics' => $agent->topics->pluck('name'),
                'metrics' => [
                    'open_tickets' => $openTickets,
                    'resolved_tickets' => $resolvedTickets,
                    'resolved_by_urgency' => $resolvedByUrgency,
                    'backlog_weight' => $backlogWeight,
                    'transfers_out' => $transfersOutCount,
                    'efficiency_percentage' => $efficiencyPercentage,
                    'resolved_last_week' => $resolvedLastWeek,
                    'burndown_data' => $burndownData
                ]
            ];
        });

        // Calculate global aggregates
        $globalMetrics = [
            'total_open' => $agents->sum('metrics.open_tickets'),
            'total_resolved' => $agents->sum('metrics.resolved_tickets'),
            'average_efficiency' => $agents->count() > 0 ? round($agents->avg('metrics.efficiency_percentage'), 1) : 100,
            'total_transfers_out' => $agents->sum('metrics.transfers_out'),
        ];

        return Inertia::render('Admin/AgentPerformance/Index', [
            'agents' => $agents,
            'globalMetrics' => $globalMetrics,
            'departments' => Department::orderBy('name')->get(),
            'topics' => Topic::orderBy('name')->get(),
            'filters' => $request->only(['department_id', 'topic_id']),
        ]);
    }
}
