<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\TicketObserver;

#[ObservedBy(TicketObserver::class)]
class Ticket extends Model
{
    protected $fillable = [
        'ticket_no',
        'requester_user_id',
        'department_id',
        'type_id',
        'topic_id',
        'priority',
        'priority_change_reason',
        'status',
        'subject',
        'description',
        'is_confidential_snapshot',
        'is_anonymous_snapshot',
        'sla_policy_id_snapshot',
        'current_assignee_user_id',
        'assigned_at',
        'is_escalated',
        'resolved_at',
    ];

    protected $appends = ['is_anonymous'];

    protected $casts = [
        'is_confidential_snapshot' => 'boolean',
        'is_anonymous_snapshot' => 'boolean',
        'is_escalated' => 'boolean',
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function type()
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'current_assignee_user_id');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function slaRuntime()
    {
        return $this->hasOne(TicketSlaRuntime::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(TicketStatusHistory::class);
    }

    public function assignments()
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function externalLinks()
    {
        return $this->hasMany(ExternalLink::class);
    }

    /**
     * Determine if the requester should be anonymized for the given user email.
     */
    public function shouldAnonymize(?string $userEmail): bool
    {
        if (!$this->is_anonymous_snapshot) {
            return false;
        }

        $superAdminEmail = config('app.super_admin_email', 'nisala.bandara@cloudsolutions.com.sa');
        
        return strtolower($userEmail) !== strtolower($superAdminEmail);
    }

    public function getIsAnonymousAttribute(): bool
    {
        return $this->shouldAnonymize(auth()->user()?->email);
    }
}
