<?php

namespace App\Services\Social;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPoster
{
    use FormatsSocialPost;

    public function __construct(
        protected ?string $botToken = null,
        protected ?string $chatId = null,
        protected ?string $endpoint = null,
    ) {
        $this->botToken ??= (string) config('services.telegram.bot_token');
        $this->chatId ??= (string) config('services.telegram.chat_id');
        $this->endpoint ??= (string) config('services.telegram.endpoint', 'https://api.telegram.org');
    }

    public function post(Post $post): ?string
    {
        if (blank($this->botToken) || blank($this->chatId)) {
            Log::error('Telegram posting skipped: missing bot token or chat id.');

            return null;
        }

        $response = Http::retry(3, 200, throw: false)->post($this->apiUrl(), [
            'chat_id' => $this->chatId,
            'text' => $this->textFor($post),
            'disable_web_page_preview' => false,
        ]);

        if (! $response->successful()) {
            Log::error('Telegram posting failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $id = $response->json('result.message_id');

        if ($id === null) {
            Log::error('Telegram posting failed: missing message id in response.', [
                'body' => $response->body(),
            ]);

            return null;
        }

        return (string) $id;
    }

    public function publish(Post $post): ?string
    {
        return $this->post($post);
    }

    public function send(Post $post): ?string
    {
        return $this->post($post);
    }

    protected function apiUrl(): string
    {
        $base = rtrim((string) $this->endpoint, '/');

        return "{$base}/bot{$this->botToken}/sendMessage";
    }
}
