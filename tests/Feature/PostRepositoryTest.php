<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Persistence\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_exists_by_link(): void
    {
        $repository = new PostRepository;

        $this->assertFalse($repository->existsByLink('https://example.com/missing'));

        Post::create(['link' => 'https://example.com/exists', 'title' => 'Exists', 'status' => PostStatus::New]);

        $this->assertTrue($repository->existsByLink('https://example.com/exists'));
    }

    public function test_save_scored_creates_post_with_new_status(): void
    {
        $repository = new PostRepository;

        $post = $repository->saveScored([
            'title' => 'Scored title',
            'link' => 'https://example.com/scored',
            'description' => 'Description',
            'pubDate' => '2026-01-01',
            'image' => null,
            'source' => 'Feed',
        ], 88);

        $this->assertSame('Scored title', $post->title);
        $this->assertSame(88, $post->interest_score);
        $this->assertSame(PostStatus::New, $post->status);
        $this->assertDatabaseHas('posts', [
            'link' => 'https://example.com/scored',
            'status' => 'new',
        ]);
    }
}
