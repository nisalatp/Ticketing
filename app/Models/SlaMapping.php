<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaMapping extends Model
{
    protected $fillable = [
        'department_id',
        'category_id',
        'type_id',
        'priority',
        'sla_policy_id',
    ];

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

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class);
    }
}
