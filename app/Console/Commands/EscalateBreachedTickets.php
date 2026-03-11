<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EscalateBreachedTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:escalate-breached';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically escalate tickets that have breached their SLA resolution time.';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\TicketRoutingService $routingService)
    {
        $breachedTickets = \App\Models\Ticket::whereNotNull('sla_breach_at')
            ->where('sla_breach_at', '<=', now())
            ->whereNotIn('status', ['Resolved', 'Closed'])
            ->where('is_escalated', false)
            ->get();

        if ($breachedTickets->isEmpty()) {
            $this->info('No breached tickets found for escalation.');
            return;
        }

        $this->info("Found {$breachedTickets->count()} breached tickets. Escalating...");

        foreach ($breachedTickets as $ticket) {
            $routingService->escalate($ticket);
            $ticket->update(['is_escalated' => true]);
            $this->line("Escalated ticket #{$ticket->ticket_no}");
        }

        $this->info('Auto-escalation process completed.');
    }
}
