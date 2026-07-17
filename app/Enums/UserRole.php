<?php

namespace App\Enums;

enum UserRole: string
{
    case Sales = 'sales';
    case Engineer = 'engineer';

    public function label(): string
    {
        return $this === self::Sales ? '営業' : 'SE';
    }
}
