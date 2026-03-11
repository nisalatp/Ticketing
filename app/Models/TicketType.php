<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'is_confidential',
        'allow_anonymous',
        'icon',
        'color_code',
        'severity_factor',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'allow_anonymous' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'type_id');
    }
}
