<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:cleanup')]
#[Description('Remove posts with trash status.')]
class CleanupPostsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = Post::query()
            ->where('status', PostStatus::Trash->value)
            ->delete();

        $this->info("Removed {$deleted} trashed post(s).");

        return self::SUCCESS;
    }
}
