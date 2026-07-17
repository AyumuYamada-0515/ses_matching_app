<?php
namespace App\Policies;
use App\Enums\InterestStatus;
use App\Models\Interest;
use App\Models\User;
class InterestPolicy
{
    public function update(User $user, Interest $interest): bool { return $user->isSales() && $interest->project()->where('sales_user_id', $user->id)->exists(); }
    public function cancel(User $user, Interest $interest): bool { return $user->isEngineer() && $interest->engineer_id === $user->id && in_array($interest->status, [InterestStatus::Pending, InterestStatus::Reviewing], true); }
}
