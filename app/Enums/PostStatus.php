<?php

namespace App\Enums;

enum PostStatus: string
{
    case New = 'new';

    public static function default(): self
    {
        return self::New;
    }
}
