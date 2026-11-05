<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attributes that can be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'profile_photo',
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'last_login_at',
    ];

    /**
     * Attributes hidden from serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Children created by this user.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Child::class, 'created_by');
    }

    /**
     * Events created by this user.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Attendance records marked by this user.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'marked_by');
    }

    /**
     * Follow-ups assigned to this user.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assigned_to');
    }

    public function assignedEventFollowUps(): HasMany
    {
        return $this->hasMany(EventFollowUp::class, 'assigned_to');
    }

    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(
            QuestionnaireResponse::class,
            'submitted_by'
        );
    }
    /**
     * Audit logs created by this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check whether the user is a child officer.
     */
    public function isChildOfficer(): bool
    {
        return $this->role === 'child_officer';
    }

    /**
     * Check whether the user is a field officer.
     */
    public function isFieldOfficer(): bool
    {
        return $this->role === 'field_officer';
    }

    /**
     * Check whether the user is an enumerator.
     */
    public function isEnumerator(): bool
    {
        return $this->role === 'enumerator';
    }

    /**
     * Check whether the user is a field member.
     */
    public function isFieldMember(): bool
    {
        return $this->role === 'field_member';
    }

    /**
     * Check whether the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}