<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'is_shared_service'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('level_id')->withTimestamps();
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function categories()
    {
        return $this->hasMany(TicketCategory::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function levels()
    {
        return $this->hasMany(DepartmentLevel::class)->orderBy('rank');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
