<?php

namespace App\Enums;

enum PostStatus: string
{
    case New = 'new';
    case Trash = 'trash';

    public static function default(): self
    {
        return self::New;
    }
}
