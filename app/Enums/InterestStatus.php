<?php

namespace App\Enums;

enum InterestStatus: string
{
    case Pending = 'pending';
    case Reviewing = 'reviewing';
    case Matched = 'matched';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '営業確認待ち',
            self::Reviewing => '確認中',
            self::Matched => 'マッチ成立',
            self::Rejected => '見送り',
            self::Cancelled => 'キャンセル',
            self::Completed => '案件終了',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedSalesTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Reviewing, self::Matched, self::Rejected],
            self::Reviewing => [self::Matched, self::Rejected],
            self::Matched => [self::Completed],
            self::Rejected, self::Cancelled, self::Completed => [],
        };
    }

    public function canSalesTransitionTo(self $status): bool
    {
        return $status === $this || in_array($status, $this->allowedSalesTransitions(), true);
    }
}
