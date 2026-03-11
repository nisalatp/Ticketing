<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalationRule extends Model
{
    protected $fillable = [
        'sla_policy_id',
        'trigger',
        'after_minutes',
        'escalate_to',
        'target_id',
        'action',
    ];

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
