<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = [
        'name',
        'response_minutes',
        'resolution_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopes()
    {
        return $this->hasMany(SlaPolicyScope::class);
    }

    public function mappings()
    {
        return $this->hasMany(SlaMapping::class);
    }

    public function escalationRules()
    {
        return $this->hasMany(EscalationRule::class);
    }
}
