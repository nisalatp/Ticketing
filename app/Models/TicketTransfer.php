<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTransfer extends Model
{
    protected $fillable = [
        'ticket_id',
        'from_user_id',
        'to_user_id',
        'transfer_reason_id',
        'notes',
        'status',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELED = 'canceled';

    public function ticket()
    {
        return $this->belongsTo(\App\Models\Ticket::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'to_user_id');
    }

    public function transferReason()
    {
        return $this->belongsTo(\App\Models\TransferReason::class);
    }
}
