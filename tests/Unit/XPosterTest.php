<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Services\Social\XPoster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XPosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_title_description_and_link(): void
    {
        Http::fake([
            '*' => Http::response(['data' => ['id' => 'x-123']], 200),
        ]);

        $poster = new XPoster('token', 'https://example.com/tweets');

        $post = new Post([
            'title' => 'Title',
            'description' => 'Description',
            'link' => 'https://example.com/post',
        ]);

        $this->assertSame('x-123', $poster->post($post));

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://example.com/tweets'
                && $request['text'] === "Title\nDescription\nhttps://example.com/post"
                && $request->header('Authorization')[0] === 'Bearer token';
        });
    }

    public function test_returns_null_on_failure(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $poster = new XPoster('token', 'https://example.com/tweets');
        $post = new Post(['title' => 'Title']);

        $this->assertNull($poster->post($post));
    }
}
