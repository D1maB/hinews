<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
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
