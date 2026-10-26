<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublishPostCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_ready_post_and_stores_social_ids(): void
    {
        config([
            'services.x.bearer_token' => 'token',
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => 'chat-1',
        ]);

        Http::fake([
            '*' => Http::response(['data' => ['id' => 'x-1'], 'result' => ['message_id' => 9]], 200),
        ]);

        Post::create([
            'title' => 'Ready post',
            'link' => 'https://example.com/ready',
            'status' => PostStatus::Scheduled,
            'scheduled_at' => now('UTC')->subMinute(),
        ]);

        $this->artisan('post:publish')->assertExitCode(0);

        $this->assertDatabaseHas('posts', [
            'title' => 'Ready post',
            'status' => PostStatus::Published->value,
            'x_id' => 'x-1',
            'telegram_id' => '9',
        ]);
    }

    public function test_does_nothing_when_no_post_ready(): void
    {
        $this->artisan('post:publish')->assertExitCode(0);
    }
}
