<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('post:publish')]
#[Description('Publish a single scheduled post.')]
class PublishPostCommand extends Command
{
    public function handle(): int
    {
        $post = Post::query()
            ->where('status', PostStatus::Scheduled->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now('UTC'))
            ->orderBy('scheduled_at')
            ->first();

        if ($post === null) {
            $this->info('No scheduled post ready to publish.');

            return self::SUCCESS;
        }

        $this->line("Published: {$post->title}");

        $post->update([
            'status' => PostStatus::Published->value,
            'published_at' => now('UTC'),
        ]);

        return self::SUCCESS;
    }
}
