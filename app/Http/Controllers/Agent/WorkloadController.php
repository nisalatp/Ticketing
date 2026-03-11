<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class WorkloadController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get count of tickets by status for this agent
        $statusCounts = Ticket::where('current_assignee_user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get count of tickets by priority
        $priorityCounts = Ticket::where('current_assignee_user_id', $user->id)
            ->whereIn('status', ['New', 'Assigned', 'In Progress', 'Waiting'])
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // High priority list
        $urgentTickets = Ticket::where('current_assignee_user_id', $user->id)
            ->whereIn('status', ['New', 'Assigned', 'In Progress', 'Waiting'])
            ->whereIn('priority', ['High', 'Urgent'])
            ->with(['requester', 'department'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Advanced Metrics
        $backlogWeight = Ticket::where('current_assignee_user_id', $user->id)
            ->whereIn('status', ['New', 'Assigned', 'In Progress', 'Waiting'])
            ->sum('weight');

        $resolvedTickets = Ticket::where('current_assignee_user_id', $user->id)
            ->whereIn('status', ['Resolved', 'Closed'])
            ->whereNotNull('resolved_at')
            ->get();

        $breachedCount = 0;
        foreach ($resolvedTickets as $t) {
            if ($t->sla_breach_at && $t->resolved_at > $t->sla_breach_at) {
                $breachedCount++;
            }
        }
        $efficiency = $resolvedTickets->count() > 0 ? round((($resolvedTickets->count() - $breachedCount) / $resolvedTickets->count()) * 100) : 100;

        $openedLast7Days = Ticket::where('current_assignee_user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))->count();
        $closedLast7Days = Ticket::where('current_assignee_user_id', $user->id)
            ->where('resolved_at', '>=', now()->subDays(7))
            ->whereIn('status', ['Resolved', 'Closed'])->count();
            
        $burndownRate = $openedLast7Days > 0 ? round(($closedLast7Days / $openedLast7Days) * 100) : ($closedLast7Days > 0 ? 100 : 0);

        $resolvedByUrgency = [
            'Urgent' => $resolvedTickets->where('priority', 'Urgent')->count(),
            'High' => $resolvedTickets->where('priority', 'High')->count(),
            'Medium' => $resolvedTickets->where('priority', 'Medium')->count(),
            'Low' => $resolvedTickets->where('priority', 'Low')->count(),
        ];
        
        $transfersOut = \App\Models\TicketTransfer::where('from_user_id', $user->id)->count();

        return Inertia::render('Agent/Workload', [
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'urgentTickets' => $urgentTickets,
            'metrics' => [
                'backlogWeight' => floatval($backlogWeight),
                'efficiency' => $efficiency,
                'burndownRate' => $burndownRate,
                'openedLast7Days' => $openedLast7Days,
                'closedLast7Days' => $closedLast7Days,
                'resolvedByUrgency' => $resolvedByUrgency,
                'transfersOut' => $transfersOut,
            ]
        ]);
    }
}
