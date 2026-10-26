<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:publish')]
#[Description('Publish scheduled posts to configured socials.')]
class PublishPostsCommand extends Command
{
    public function handle(): int
    {
        $posts = Post::query()
            ->where('status', PostStatus::Scheduled->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now('UTC'))
            ->orderBy('scheduled_at')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts ready to publish.');

            return self::SUCCESS;
        }

        $published = 0;

        foreach ($posts as $post) {
            $this->line("Published: {$post->title}");

            $post->update([
                'status' => PostStatus::Published->value,
                'published_at' => now('UTC'),
            ]);

            $published++;
        }

        $this->info("Published {$published} post(s).");

        return self::SUCCESS;
    }
}
