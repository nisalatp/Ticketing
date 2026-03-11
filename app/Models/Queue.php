<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'assignment_strategy',
        'lead_user_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function categories()
    {
        return $this->hasMany(TicketCategory::class, 'default_queue_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
