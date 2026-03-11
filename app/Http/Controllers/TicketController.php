<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Department;
use App\Models\TicketType;
use App\Models\TicketCategory;
use App\Services\TicketRoutingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    protected $routingService;

    public function __construct(TicketRoutingService $routingService)
    {
        $this->routingService = $routingService;
    }

    public function index()
    {
        $user = Auth::user();
        
        $allTickets = Ticket::where('requester_user_id', $user->id)
            ->with(['requester', 'department', 'type', 'topic', 'assignee'])
            ->orderByDesc('created_at')
            ->get();

        $groupedTickets = [
            'unassigned' => $allTickets->filter(fn($t) => is_null($t->current_assignee_user_id) && !in_array($t->status, ['Resolved', 'Closed']))->values(),
            'progressing' => $allTickets->filter(fn($t) => !is_null($t->current_assignee_user_id) && !in_array($t->status, ['Resolved', 'Closed']))->values(),
            'completed' => $allTickets->filter(fn($t) => in_array($t->status, ['Resolved', 'Closed']))->values(),
        ];

        return Inertia::render('Tickets/Index', [
            'groupedTickets' => $groupedTickets,
        ]);
    }

    public function create()
    {
        return Inertia::render('Tickets/Create', [
            'departments' => Department::with('topics')->where('is_shared_service', false)->get(),
            'ticketTypes' => TicketType::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'topic_id' => 'nullable|exists:topics,id',
            'type_id' => 'required|exists:ticket_types,id',
            'priority' => 'required|string|in:Low,Medium,High,Urgent',
            'is_anonymous' => 'nullable|boolean',
            'attachments.*' => 'nullable|file',
        ]);



        // Ensure the selected type is associated with the selected category (or just use the category of the type if it exists)
        $selectedType = TicketType::find($request->type_id);

        $ticket = Ticket::create([
            'ticket_no' => 'TKT-' . strtoupper(Str::random(8)),
            'requester_user_id' => Auth::id(),
            'department_id' => $request->department_id,
            'topic_id' => $request->topic_id,
            'type_id' => $request->type_id,
            'priority' => $request->priority,
            'is_anonymous_snapshot' => $request->boolean('is_anonymous'),
            'status' => 'Pending',
            'subject' => $request->subject,
            'description' => $request->description,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $ticket->attachments()->create([
                    'uploader_user_id' => Auth::id(),
                    'path' => $file->store('ticket-attachments', 'public'),
                    'filename' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Route the ticket
        $this->routingService->route($ticket);

        return redirect()->route('tickets.show', $ticket->id)->with('message', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        if ($ticket->requester_user_id !== Auth::id() && !$ticket->assignee && !Auth::user()->is_admin) {
             // simplified auth check
             if ($ticket->requester_user_id !== Auth::id() && $ticket->current_assignee_user_id !== Auth::id()) {
                 abort(403);
             }
        }

        $ticket->load(['department', 'type', 'assignee', 'topic', 'messages.user', 'messages.attachments', 'attachments']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function storeMessage(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file',
        ]);

        if ($ticket->requester_user_id !== Auth::id()) {
            abort(403);
        }

        $message = $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'visibility' => 'public',
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

        if ($ticket->assignee) {
            $ticket->assignee->notify(new \App\Notifications\TicketEventNotification(
                $ticket,
                'new_message',
                "New message from requester on ticket {$ticket->ticket_no}."
            ));
        }

        return redirect()->back()->with('message', 'Reply sent successfully.');
    }
}
