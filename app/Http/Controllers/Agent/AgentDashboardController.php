<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\TicketTransfer;

class AgentDashboardController extends Controller
{
    protected $routingService;

    public function __construct(\App\Services\TicketRoutingService $routingService)
    {
        $this->routingService = $routingService;
    }

    public function index()
    {
        $user = Auth::user();

        // Get tickets assigned to the current agent
        // For Resolved/Closed tickets, only show those updated within the last 24 hours
        $tickets = Ticket::where('current_assignee_user_id', $user->id)
            ->with(['requester', 'department', 'category', 'topic', 'type', 'messages.user', 'messages.attachments'])
            ->where(function ($query) {
                $query->whereNotIn('status', ['Resolved', 'Closed'])
                    ->orWhere(function ($q) {
                        $q->whereIn('status', ['Resolved', 'Closed'])
                          ->where('updated_at', '>=', now()->subHours(24));
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get tickets pending receiving transfer to this agent
        $incomingTransfers = TicketTransfer::where('to_user_id', $user->id)
            ->where('status', TicketTransfer::STATUS_PENDING)
            ->with('ticket.requester', 'ticket.department', 'ticket.category', 'ticket.topic', 'ticket.type', 'transferReason')
            ->get()
            ->pluck('ticket');

        // Get tickets the current agent has initiated a transfer for
        $outgoingTransfers = TicketTransfer::where('from_user_id', $user->id)
            ->where('status', TicketTransfer::STATUS_PENDING)
            ->with('ticket.requester', 'ticket.department', 'ticket.category', 'ticket.topic', 'ticket.type', 'toUser', 'transferReason')
            ->get()
            ->pluck('ticket');

        // Filter out $tickets that are also in $outgoingTransfers to avoid rendering them twice
        $outgoingTicketIds = $outgoingTransfers->pluck('id')->toArray();
        $tickets = $tickets->reject(function ($ticket) use ($outgoingTicketIds) {
            return in_array($ticket->id, $outgoingTicketIds);
        })->values();

        return Inertia::render('Agent/Dashboard', [
            'tickets' => $tickets,
            'incoming' => $incomingTransfers,
            'transfers' => $outgoingTransfers,
            'users' => \App\Models\User::has('departments')->where('id', '!=', $user->id)->get(['id', 'name', 'job_title']),
            'transferReasons' => \App\Models\TransferReason::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string|in:Assigned,In Progress,Waiting,Resolved,Closed',
        ]);

        if ($ticket->current_assignee_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $data = ['status' => $request->status];
        if (in_array($request->status, ['Resolved', 'Closed'])) {
            $data['resolved_at'] = now();
        } else {
            $data['resolved_at'] = null; // Re-opened
        }
        $ticket->update($data);

        if (in_array($request->status, ['Resolved', 'Closed'])) {
            $ticket->requester->notify(new \App\Notifications\TicketEventNotification(
                $ticket, 
                'status_changed', 
                "Your ticket {$ticket->ticket_no} has been marked as {$request->status}."
            ));
        }

        return redirect()->back()->with('message', 'Ticket status updated.');
    }

    public function storeMessage(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB limit
        ]);

        $message = $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'visibility' => 'internal', // Default for agent comments
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                $message->attachments()->create([
                    'ticket_id' => $ticket->id,
                    'uploader_user_id' => Auth::id(),
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->back()->with('message', 'Comment added.');
    }

    public function escalate(Request $request, Ticket $ticket)
    {
        if ($ticket->current_assignee_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $success = $this->routingService->escalate($ticket);

        if ($success) {
            return redirect()->back()->with('message', 'Ticket escalated successfully.');
        }

        return redirect()->back()->with('error', 'Could not escalate ticket. No higher level or Shared Services fallback available.');
    }

    public function initiateTransfer(Request $request, Ticket $ticket)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'transfer_reason_id' => 'required|exists:transfer_reasons,id',
            'notes' => 'nullable|string',
        ]);

        if ($ticket->current_assignee_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        // Check if there is already a pending transfer
        if (TicketTransfer::where('ticket_id', $ticket->id)->where('status', TicketTransfer::STATUS_PENDING)->exists()) {
            return redirect()->back()->with('error', 'Ticket already has a pending transfer.');
        }

        TicketTransfer::create([
            'ticket_id' => $ticket->id,
            'from_user_id' => Auth::id(),
            'to_user_id' => $request->to_user_id,
            'transfer_reason_id' => $request->transfer_reason_id,
            'notes' => $request->notes,
            'status' => TicketTransfer::STATUS_PENDING,
        ]);

        // Optional: Notify Receiver

        return redirect()->back()->with('message', 'Transfer initiated.');
    }

    public function acceptTransfer(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string|in:Assigned,In Progress,Waiting,Resolved,Closed',
        ]);

        $transfer = TicketTransfer::where('ticket_id', $ticket->id)
            ->where('to_user_id', Auth::id())
            ->where('status', TicketTransfer::STATUS_PENDING)
            ->first();

        if (!$transfer) {
            return redirect()->back()->with('error', 'No pending transfer found for this ticket.');
        }

        $transfer->update(['status' => TicketTransfer::STATUS_ACCEPTED]);
        
        $data = [
            'current_assignee_user_id' => Auth::id(),
            'status' => $request->status,
        ];

        if (in_array($request->status, ['Resolved', 'Closed'])) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        // Add a system message logging the transfer
        $ticket->messages()->create([
            'user_id' => Auth::id(), // Or system user id if applicable
            'message' => "Ticket formally transferred from {$transfer->fromUser->name} to {$transfer->toUser->name} based on reason: {$transfer->transferReason->name}.",
            'visibility' => 'internal',
        ]);

        return redirect()->back()->with('message', 'Transfer accepted and status updated.');
    }

    public function rejectTransfer(Ticket $ticket)
    {
        $transfer = TicketTransfer::where('ticket_id', $ticket->id)
            ->where('to_user_id', Auth::id())
            ->where('status', TicketTransfer::STATUS_PENDING)
            ->first();

        if (!$transfer) {
            return redirect()->back()->with('error', 'No pending transfer found for this ticket.');
        }

        $transfer->update(['status' => TicketTransfer::STATUS_REJECTED]);

        // Optional: Notify Sender that it was rejected

        return redirect()->back()->with('message', 'Transfer rejected.');
    }

    public function cancelTransfer(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string|in:Assigned,In Progress,Waiting,Resolved,Closed',
        ]);

        $transfer = TicketTransfer::where('ticket_id', $ticket->id)
            ->where('from_user_id', Auth::id())
            ->where('status', TicketTransfer::STATUS_PENDING)
            ->first();

        if ($transfer) {
            $transfer->update(['status' => TicketTransfer::STATUS_CANCELED]);
        }
        
        $data = ['status' => $request->status];
        if (in_array($request->status, ['Resolved', 'Closed'])) {
            $data['resolved_at'] = now();
        } else {
            $data['resolved_at'] = null;
        }
        $ticket->update($data);

        return redirect()->back()->with('message', 'Transfer canceled.');
    }

    public function previousTickets(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::where('current_assignee_user_id', $user->id)
            ->whereIn('status', ['Resolved', 'Closed'])
            ->with(['requester', 'department', 'topic', 'type']);

        // Filtering
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ticket_no', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        return Inertia::render('Agent/PreviousTickets', [
            'tickets' => $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString(),
            'filters' => $request->only(['type_id', 'topic_id', 'date_from', 'date_to', 'search']),
            'types' => \App\Models\TicketType::all(),
            'topics' => \App\Models\Topic::all(),
        ]);
    }
}
