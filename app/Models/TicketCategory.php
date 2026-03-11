<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'default_queue_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function defaultQueue()
    {
        return $this->belongsTo(Queue::class, 'default_queue_id');
    }

    public function types()
    {
        return $this->hasMany(TicketType::class, 'category_id');
    }

    public function ticketTypes()
    {
        return $this->types();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}
