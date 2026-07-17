<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '下書き', self::Open => '公開中', self::Closed => '募集終了'
        };
    }
}
