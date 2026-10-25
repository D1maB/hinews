<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $casts = [
        'status' => PostStatus::class,
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
    ];
}
