<?php

namespace App\Models;

use App\Enums\AssignmentInvitationStatus;
use Illuminate\Database\Eloquent\Model;

class AssignmentInvitation extends Model
{
    protected $fillable = ['sales_user_id', 'engineer_id', 'status', 'responded_at'];

    protected function casts(): array
    {
        return ['status' => AssignmentInvitationStatus::class, 'responded_at' => 'datetime'];
    }

    public function salesRepresentative()
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
}
