<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\SlaPolicy;
use App\Models\TicketType;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        $this->calculateSlaAndWeight($ticket);
        
        if ($ticket->current_assignee_user_id) {
            $ticket->assigned_at = now();
        }
    }

    /**
     * Handle the Ticket "updating" event.
     */
    public function updating(Ticket $ticket): void
    {
        if ($ticket->isDirty(['priority', 'department_id', 'type_id', 'topic_id'])) {
            $this->calculateSlaAndWeight($ticket);
        }

        if ($ticket->isDirty('current_assignee_user_id')) {
            $ticket->assigned_at = $ticket->current_assignee_user_id ? now() : null;
        }
    }

    protected function calculateSlaAndWeight(Ticket $ticket): void
    {
        $businessHours = app(\App\Services\BusinessHoursService::class);

        // Calculate Weight based on Priority
        $weightMap = [
            'Low' => 1.0,
            'Medium' => 2.0,
            'High' => 3.0,
            'Urgent' => 5.0,
        ];
        
        $baseWeight = $weightMap[$ticket->priority ?? 'Medium'] ?? 2.0;
        
        // Multiply by severity_factor if ticket type is loaded
        if ($ticket->type_id) {
            $type = \App\Models\TicketType::find($ticket->type_id);
            if ($type && isset($type->severity_factor)) {
                $baseWeight *= $type->severity_factor;
            }
        }
        
        $ticket->weight = $baseWeight;

        // Calculate SLA Breach if not resolved
        if (!in_array($ticket->status, ['Resolved', 'Closed'])) {
            // Find best matching SLA policy scope
            $policyScope = \App\Models\SlaPolicyScope::whereHas('policy', function($q) {
                    $q->where('is_active', true);
                })
                ->where(function($q) use ($ticket) {
                    $q->where('priority', $ticket->priority)
                      ->orWhereNull('priority');
                })
                ->where(function($query) use ($ticket) {
                    $query->where(function($q) use ($ticket) {
                        $q->where('department_id', $ticket->department_id)
                          ->where('topic_id', $ticket->topic_id)
                          ->where('type_id', $ticket->type_id);
                    })->orWhere(function($q) use ($ticket) {
                        $q->where('department_id', $ticket->department_id)
                          ->where('topic_id', $ticket->topic_id)
                          ->whereNull('type_id');
                    })->orWhere(function($q) use ($ticket) {
                        $q->where('department_id', $ticket->department_id)
                          ->whereNull('topic_id')
                          ->where('type_id', $ticket->type_id);
                    })->orWhere(function($q) use ($ticket) {
                        $q->where('department_id', $ticket->department_id)
                          ->whereNull('topic_id')
                          ->whereNull('type_id');
                    })->orWhere(function($q) use ($ticket) {
                        $q->whereNull('department_id')
                          ->where('topic_id', $ticket->topic_id)
                          ->where('type_id', $ticket->type_id);
                    })->orWhere(function($q) use ($ticket) {
                        $q->whereNull('department_id')
                          ->whereNull('topic_id')
                          ->where('type_id', $ticket->type_id);
                    })->orWhere(function($q) {
                        $q->whereNull('department_id')
                          ->whereNull('topic_id')
                          ->whereNull('type_id');
                    });
                })
                ->orderByRaw('department_id IS NOT NULL DESC, topic_id IS NOT NULL DESC, type_id IS NOT NULL DESC, priority IS NOT NULL DESC')
                ->first();

            $policy = $policyScope ? $policyScope->policy : null;
            $baseTime = $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at) : now();

            if ($policy) {
                // Use BusinessHoursService to add resolution minutes
                $ticket->sla_breach_at = $businessHours->addBusinessMinutes($baseTime, $policy->resolution_minutes);
                $ticket->sla_policy_id_snapshot = $policy->id;
            } else {
                // Default Fallback SLA: 24 business hours for normal, 4 for urgent
                $hours = ($ticket->priority === 'Urgent' || $ticket->priority === 'High') ? 4 : 24;
                $ticket->sla_breach_at = $businessHours->addBusinessMinutes($baseTime, $hours * 60);
            }
        }
    }
}
