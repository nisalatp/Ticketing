<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'ticket_id',
        'message_id',
        'uploader_user_id',
        'path',
        'filename',
        'mime',
        'size',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function message()
    {
        return $this->belongsTo(TicketMessage::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_user_id');
    }
}
