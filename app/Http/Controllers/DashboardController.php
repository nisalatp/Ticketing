<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $statusFilter = $request->input('status', 'All');

        // Basic stats for the user's tickets
        $totalTickets = Ticket::where('requester_user_id', $user->id)->count();
        $openTickets = Ticket::where('requester_user_id', $user->id)
            ->whereIn('status', ['New', 'Assigned', 'In Progress', 'Waiting'])
            ->count();
        $resolvedTickets = Ticket::where('requester_user_id', $user->id)
            ->where('status', 'Resolved')
            ->count();
            
        // "Escalated" meaning in this context might be tickets moved to a higher level.
        // For simplicity, we can count high priority or actually escalated based on events.
        // As a proxy, let's use High/Urgent priority open tickets.
        $escalatedTickets = Ticket::where('requester_user_id', $user->id)
            ->whereIn('status', ['New', 'Assigned', 'In Progress', 'Waiting'])
            ->whereIn('priority', ['High', 'Urgent'])
            ->count();

        // All tickets for grouping
        $allTickets = Ticket::where('requester_user_id', $user->id)
            ->with(['type'])
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedTickets = [
            'unassigned' => $allTickets->filter(fn($t) => is_null($t->current_assignee_user_id) && !in_array($t->status, ['Resolved', 'Closed']))->values(),
            'progressing' => $allTickets->filter(fn($t) => !is_null($t->current_assignee_user_id) && !in_array($t->status, ['Resolved', 'Closed']))->values(),
            'completed' => $allTickets->filter(fn($t) => in_array($t->status, ['Resolved', 'Closed']))->values(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalTickets,
                'open' => $openTickets,
                'resolved' => $resolvedTickets,
                'escalated' => $escalatedTickets,
            ],
            'groupedTickets' => $groupedTickets,
            'filters' => [
                'status' => $statusFilter
            ]
        ]);
    }
}
