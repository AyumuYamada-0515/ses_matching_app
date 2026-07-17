<?php

namespace App\Models;

use App\Enums\InterestStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'profile'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'role' => UserRole::class];
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'sales_user_id');
    }

    public function assignedEngineers()
    {
        return $this->belongsToMany(User::class, 'engineer_sales', 'sales_user_id', 'engineer_id')->withTimestamps();
    }

    public function salesRepresentatives()
    {
        return $this->belongsToMany(User::class, 'engineer_sales', 'engineer_id', 'sales_user_id')->withTimestamps();
    }

    public function interests()
    {
        return $this->hasMany(Interest::class, 'engineer_id');
    }

    public function sentAssignmentInvitations()
    {
        return $this->hasMany(AssignmentInvitation::class, 'sales_user_id');
    }

    public function receivedAssignmentInvitations()
    {
        return $this->hasMany(AssignmentInvitation::class, 'engineer_id');
    }

    public function isSales(): bool
    {
        return $this->role === UserRole::Sales;
    }

    public function isEngineer(): bool
    {
        return $this->role === UserRole::Engineer;
    }

    public function hasActiveMatch(): bool
    {
        return $this->interests()->where('status', InterestStatus::Matched->value)->exists();
    }
}
