<?php

namespace App\Models;

use App\Enums\InterestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;

    public const DEFAULT_MESSAGE = "こちらの案件が気になっています。\n詳細についてお話を伺いたいです。";

    protected $fillable = ['project_id', 'engineer_id', 'message', 'status'];

    protected function casts(): array
    {
        return ['status' => InterestStatus::class];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
}
