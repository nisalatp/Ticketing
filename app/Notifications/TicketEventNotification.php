<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketEventNotification extends Notification
{
    use Queueable;

    public $ticket;
    public $eventType;
    public $message;

    public function __construct(Ticket $ticket, string $eventType, string $message)
    {
        $this->ticket = $ticket;
        $this->eventType = $eventType;
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_no' => $this->ticket->ticket_no,
            'event_type' => $this->eventType,
            'message' => $this->message,
            'created_by' => auth()->id() ?? $this->ticket->requester_user_id,
        ];
    }
}
