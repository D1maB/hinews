<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $casts = [
        'status' => PostStatus::class,
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'embedding' => 'array',
    ];

    protected $fillable = [
        'title',
        'link',
        'description',
        'pub_date',
        'image',
        'source',
        'interest_score',
        'status',
        'scheduled_at',
        'published_at',
        'x_id',
        'telegram_id',
        'embedding',
    ];
}
