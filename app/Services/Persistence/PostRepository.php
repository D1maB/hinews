<?php

namespace App\Services\Persistence;

use App\Enums\PostStatus;
use App\Models\Post;

class PostRepository
{
    public function existsByLink(?string $link): bool
    {
        if (! $link) {
            return false;
        }

        return Post::where('link', $link)->exists();
    }

    public function saveScored(array $item, int $score): Post
    {
        return Post::updateOrCreate(
            ['link' => $item['link']],
            [
                'title' => $item['title'] ?? 'Untitled',
                'description' => $item['description'] ?? null,
                'pub_date' => $item['pubDate'] ?? null,
                'image' => $item['image'] ?? null,
                'source' => $item['source'] ?? null,
                'interest_score' => $score,
                'status' => PostStatus::New,
            ]
        );
    }
}
