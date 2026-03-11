<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSlaRuntime extends Model
{
    protected $table = 'ticket_sla_runtime';

    protected $fillable = [
        'ticket_id',
        'response_due_at',
        'resolution_due_at',
        'first_response_at',
        'resolved_at',
        'response_breached',
        'resolution_breached',
    ];

    protected $casts = [
        'response_due_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'response_breached' => 'boolean',
        'resolution_breached' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
