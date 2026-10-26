<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Social\TelegramPoster;
use App\Services\Social\XPoster;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('post:publish')]
#[Description('Publish a single scheduled post.')]
class PublishPostCommand extends Command
{
    public function __construct(
        protected XPoster $x,
        protected TelegramPoster $telegram,
    ) {
        parent::__construct();
    }

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

        $xId = $this->x->post($post);
        $telegramId = $this->telegram->post($post);

        $post->update([
            'status' => PostStatus::Published->value,
            'published_at' => now('UTC'),
            'x_id' => $xId,
            'telegram_id' => $telegramId,
        ]);

        $this->line("Published: {$post->title}");

        return self::SUCCESS;
    }
}
