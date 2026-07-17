<?php

namespace App\Enums;

enum AssignmentInvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '回答待ち', self::Accepted => '承諾', self::Rejected => '拒否'
        };
    }
}
