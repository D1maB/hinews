<?php

namespace App\Services\Social;

use App\Models\Post;

trait FormatsSocialPost
{
    protected function textFor(Post $post): string
    {
        return collect([$post->title, $post->description, $post->link])
            ->filter()
            ->implode("\n");
    }
}
