<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => ProjectStatus::class, 'start_date' => 'date', 'application_deadline' => 'date'];
    }

    public function salesUser()
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function interests()
    {
        return $this->hasMany(Interest::class);
    }

    public function isAcceptingApplications(): bool
    {
        return $this->status === ProjectStatus::Open && ! $this->application_deadline->isPast();
    }
}
