<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_feeds_parse_stores_posts(): void
    {
        $this->artisan('feeds:parse')->assertExitCode(0);

        $this->assertGreaterThan(0, Post::count());
    }

    public function test_feeds_parse_does_not_create_duplicates(): void
    {
        $this->artisan('feeds:parse')->assertExitCode(0);
        $firstCount = Post::count();

        $this->artisan('feeds:parse')->assertExitCode(0);

        $this->assertSame($firstCount, Post::count());
    }
}
