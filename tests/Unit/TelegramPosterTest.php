<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Services\Social\TelegramPoster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramPosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_title_description_and_link(): void
    {
        Http::fake([
            '*' => Http::response(['result' => ['message_id' => 456]], 200),
        ]);

        $poster = new TelegramPoster('bot-token', 'chat-1', 'https://api.telegram.org');

        $post = new Post([
            'title' => 'Title',
            'description' => 'Description',
            'link' => 'https://example.com/post',
        ]);

        $this->assertSame('456', $poster->post($post));

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.telegram.org/botbot-token/sendMessage'
                && $request['chat_id'] === 'chat-1'
                && $request['text'] === "Title\nDescription\nhttps://example.com/post";
        });
    }

    public function test_returns_null_on_failure(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $poster = new TelegramPoster('bot-token', 'chat-1', 'https://api.telegram.org');
        $post = new Post(['title' => 'Title']);

        $this->assertNull($poster->post($post));
    }
}
