<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\PostInterestScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreFeedsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_saves_scored_posts_as_new(): void
    {
        $scorer = new class extends PostInterestScorer {
            public function score(string $title): ?int
            {
                return 99;
            }
        };

        $this->app->instance(PostInterestScorer::class, $scorer);

        $this->artisan('feeds:score')->assertExitCode(0);

        $this->assertGreaterThan(0, Post::count());
        $this->assertTrue(Post::where('status', PostStatus::New->value)->exists());
    }
}
