<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use App\Models\TicketType;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TicketManagementController extends Controller implements HasMiddleware
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
        $query = Ticket::with(['requester', 'department', 'type', 'topic', 'assignee']);

        // Filtering
        if ($request->filled('agent_id')) {
            $query->where('current_assignee_user_id', $request->agent_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ticket_no', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        $tickets = $query->latest()->paginate(25)->withQueryString();

        // Statistics Aggregation
        $stats = [
            'by_department' => Ticket::groupBy('department_id')
                ->select('department_id', DB::raw('count(*) as count'))
                ->with('department:id,name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->department->name ?? 'Unknown' => $item->count]),
                
            'by_agent' => Ticket::groupBy('current_assignee_user_id')
                ->select('current_assignee_user_id', DB::raw('count(*) as count'))
                ->with('assignee:id,name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->assignee->name ?? 'Unassigned' => $item->count]),
                
            'by_type' => Ticket::groupBy('type_id')
                ->select('type_id', DB::raw('count(*) as count'))
                ->with('type:id,name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->type->name ?? 'None' => $item->count]),
                
            'by_topic' => Ticket::groupBy('topic_id')
                ->select('topic_id', DB::raw('count(*) as count'))
                ->with('topic:id,name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->topic->name ?? 'General' => $item->count]),
                
            'by_urgency' => Ticket::groupBy('priority')
                ->select('priority', DB::raw('count(*) as count'))
                ->get()
                ->mapWithKeys(fn($item) => [$item->priority => $item->count]),
                
            'by_status' => Ticket::groupBy('status')
                ->select('status', DB::raw('count(*) as count'))
                ->get()
                ->mapWithKeys(fn($item) => [$item->status => $item->count]),
        ];

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'filters' => $request->all(['agent_id', 'department_id', 'type_id', 'topic_id', 'priority', 'status', 'search']),
            'agents' => User::all(),
            'departments' => Department::all(),
            'types' => TicketType::all(),
            'topics' => Topic::all(),
            'transferReasons' => \App\Models\TransferReason::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'transfer_reason_id' => 'nullable|exists:transfer_reasons,id',
            'note' => 'nullable|string',
        ]);

        $previousAssigneeId = $ticket->current_assignee_user_id;
        $ticket->current_assignee_user_id = $request->user_id;
        $ticket->status = 'Assigned';
        $ticket->save();

        if ($previousAssigneeId && $previousAssigneeId != $request->user_id) {
            \App\Models\TicketTransfer::create([
                'ticket_id' => $ticket->id,
                'from_user_id' => $previousAssigneeId,
                'to_user_id' => $request->user_id,
                'transfer_reason_id' => $request->transfer_reason_id,
                'notes' => $request->note ?: 'Admin reassigned ticket manually.',
                'status' => 'Accepted',
            ]);
        }

        if ($request->note) {
            $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => "Manual Assignment Note: " . $request->note,
                'visibility' => 'internal',
            ]);
        }

        $newAssignee = User::find($request->user_id);
        if ($newAssignee) {
            $newAssignee->notify(new \App\Notifications\TicketEventNotification(
                $ticket,
                'assigned',
                "Ticket {$ticket->ticket_no} has been manually assigned to you by " . Auth::user()->name . "."
            ));
            
            $ticket->statusHistory()->create([
                'user_id' => Auth::id(),
                'changed_by_user_id' => Auth::id(),
                'status' => 'Assigned',
                'event_type' => 'assigned',
                'description' => "Manually assigned to " . $newAssignee->name,
                'from_state' => $ticket->status,
                'to_state' => 'Assigned',
            ]);
        }

        return redirect()->back()->with('message', 'Ticket assigned successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->back()->with('message', 'Ticket deleted successfully.');
    }
}
