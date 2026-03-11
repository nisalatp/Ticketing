<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentLevel;
use App\Models\Topic;
use App\Notifications\TicketEventNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TicketRoutingService
{
    /**
     * Initial routing of a ticket when it is first created or reset.
     */
    public function route(Ticket $ticket): ?User
    {
        return $this->performRouting($ticket, $ticket->department_id, "Initial routing");
    }

    /**
     * Escalate a ticket to the next hierarchical level.
     */
    public function escalate(Ticket $ticket): bool
    {
        $currentAssignee = $ticket->assignee;
        if (!$currentAssignee) {
            $ticket->update(['current_assignee_user_id' => $this->route($ticket)?->id]);
            return true;
        }

        // Get current assignee's level in this department
        $membership = $currentAssignee->departments()
            ->where('departments.id', $ticket->department_id)
            ->first();

        $currentLevelRank = $membership?->pivot?->level_id 
            ? DepartmentLevel::find($membership->pivot->level_id)?->rank 
            : 0;

        // Find next level in current department
        $nextLevel = DepartmentLevel::where('department_id', $ticket->department_id)
            ->where('rank', '>', $currentLevelRank)
            ->orderBy('rank')
            ->first();

        if ($nextLevel) {
            $newAssignee = $this->findAssigneeAtLevel($nextLevel, $ticket->topic_id, $ticket->department_id);
            if ($newAssignee) {
                $this->assign($ticket, $newAssignee, "Escalated to Level {$nextLevel->rank} ({$nextLevel->name})");
                return true;
            }
        }

        // If no next level or no assignee at next level, try to forward to Management Services
        return $this->forwardToManagementServices($ticket);
    }

    /**
     * Internal implementation of routing logic.
     */
    private function performRouting(Ticket $ticket, int $departmentId, string $context): ?User
    {
        $levels = DepartmentLevel::where('department_id', $departmentId)
            ->orderBy('rank')
            ->get();

        if ($levels->isEmpty()) {
            Log::warning("No levels defined for department ID: {$departmentId}.");
            return null;
        }

        // 1. Try topic match at each level sequentially from bottom
        foreach ($levels as $level) {
            $assignee = $this->findAssigneeAtLevel($level, $ticket->topic_id, $departmentId);
            if ($assignee) {
                $this->assign($ticket, $assignee, "{$context}: Assigned to {$level->name} (Topic Match)");
                return $assignee;
            }
        }

        // 2. Try any assignee at Level 1 (lowest) if no topic match found anywhere
        $lowestLevel = $levels->first();
        $assignee = $this->findAssigneeAtLevel($lowestLevel, null, $departmentId);
        if ($assignee) {
            $this->assign($ticket, $assignee, "{$context}: Assigned to {$lowestLevel->name} (General Fallback)");
            return $assignee;
        }

        return null;
    }

    /**
     * Forward ticket to the lowest level of the Management Services department.
     */
    private function forwardToManagementServices(Ticket $ticket): bool
    {
        $currentDept = Department::find($ticket->department_id);
        if ($currentDept && $currentDept->is_shared_service) {
            Log::info("Ticket #{$ticket->ticket_no} is already in Management Services and reached the top.");
            return false;
        }

        $managementServicesDept = Department::where('is_shared_service', true)->first();
        if (!$managementServicesDept) {
            Log::error("Management Services department not found for global escalation.");
            return false;
        }

        // Log the transition before changing department_id
        $this->logTransition($ticket, "Forwarding to Management Services department as it remains unresolved in " . ($currentDept->name ?? 'original department') . ".");

        $ticket->department_id = $managementServicesDept->id;
        $ticket->save();

        $newAssignee = $this->performRouting($ticket, $managementServicesDept->id, "Management Services Escalation");
        return (bool)$newAssignee;
    }

    /**
     * Find an available user at a specific level in a specific department.
     */
    private function findAssigneeAtLevel(DepartmentLevel $level, ?int $topicId, int $departmentId): ?User
    {
        $query = User::whereHas('departments', function($q) use ($departmentId, $level) {
                $q->where('departments.id', $departmentId)
                  ->where('department_user.level_id', $level->id);
            })
            ->where('is_active', true);

        if ($topicId) {
            $query->whereHas('topics', function ($q) use ($topicId) {
                $q->where('topics.id', $topicId);
            });
        }

        return $query->withCount(['assignedTickets' => function ($q) {
                $q->whereIn('status', ['Assigned', 'In Progress', 'Waiting']);
            }])
            ->orderBy('assigned_tickets_count', 'asc')
            ->first();
    }

    /**
     * Set assignee and log.
     */
    private function assign(Ticket $ticket, User $user, string $reason): void
    {
        $ticket->update([
            'current_assignee_user_id' => $user->id,
            'status' => 'Assigned',
        ]);

        $this->logTransition($ticket, "{$reason}. Assigned to {$user->name}.");

        $eventType = str_contains(strtolower($reason), 'escalat') ? 'escalated' : 'assigned';
        $user->notify(new TicketEventNotification($ticket, $eventType, "Ticket {$ticket->ticket_no} has been $eventType to you."));
    }

    /**
     * Create a system message for audit trail.
     */
    private function logTransition(Ticket $ticket, string $message): void
    {
        $ticket->messages()->create([
            'user_id' => null, // System
            'message' => $message,
            'visibility' => 'internal',
        ]);
    }
}
