<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicyScope extends Model
{
    protected $fillable = [
        'sla_policy_id',
        'department_id',
        'topic_id',
        'type_id',
        'priority',
    ];

    public function type()
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    public function policy()
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
