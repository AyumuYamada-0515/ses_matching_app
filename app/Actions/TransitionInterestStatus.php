<?php

namespace App\Actions;

use App\Enums\InterestStatus;
use App\Models\Interest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionInterestStatus
{
    public function handle(Interest $interest, InterestStatus $nextStatus): Interest
    {
        return DB::transaction(function () use ($interest, $nextStatus) {
            $lockedInterest = Interest::query()
                ->whereKey($interest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedInterest->status->canSalesTransitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'status' => "{$lockedInterest->status->label()}から{$nextStatus->label()}へは変更できません。",
                ]);
            }

            if ($lockedInterest->status === $nextStatus) {
                return $lockedInterest;
            }

            if ($nextStatus === InterestStatus::Matched) {
                $this->ensureMatchIsAvailable($lockedInterest);
            }

            $lockedInterest->update(['status' => $nextStatus]);

            return $lockedInterest;
        });
    }

    private function ensureMatchIsAvailable(Interest $interest): void
    {
        User::query()->whereKey($interest->engineer_id)->lockForUpdate()->firstOrFail();
        $project = Project::query()->whereKey($interest->project_id)->lockForUpdate()->firstOrFail();

        $hasAnotherMatch = Interest::query()
            ->where('engineer_id', $interest->engineer_id)
            ->where('status', InterestStatus::Matched)
            ->whereKeyNot($interest->id)
            ->exists();

        if ($hasAnotherMatch) {
            throw ValidationException::withMessages([
                'status' => 'このSEは別の案件でマッチング中です。',
            ]);
        }

        $matchedCount = Interest::query()
            ->where('project_id', $project->id)
            ->where('status', InterestStatus::Matched)
            ->count();

        if ($matchedCount >= $project->recruitment_count) {
            throw ValidationException::withMessages([
                'status' => 'この案件は募集人数に達しています。',
            ]);
        }
    }
}
