<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_trashed_posts(): void
    {
        Post::create(['title' => 'Trash 1', 'link' => 'https://example.com/trash-1', 'status' => PostStatus::Trash]);
        Post::create(['title' => 'Trash 2', 'link' => 'https://example.com/trash-2', 'status' => PostStatus::Trash]);
        Post::create(['title' => 'Keep', 'link' => 'https://example.com/keep', 'status' => PostStatus::New]);

        $this->artisan('posts:cleanup')->assertExitCode(0);

        $this->assertDatabaseMissing('posts', ['title' => 'Trash 1']);
        $this->assertDatabaseMissing('posts', ['title' => 'Trash 2']);
        $this->assertDatabaseHas('posts', ['title' => 'Keep']);
    }
}
