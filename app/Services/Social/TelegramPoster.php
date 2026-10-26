<?php

namespace App\Services\Social;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class TelegramPoster
{
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
        $response = Http::post($this->apiUrl(), [
            'chat_id' => $this->chatId,
            'text' => $this->textFor($post),
            'disable_web_page_preview' => false,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $id = $response->json('result.message_id');

        return $id === null ? null : (string) $id;
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

    protected function textFor(Post $post): string
    {
        $lines = array_filter([$post->title, $post->description, $post->link]);

        return implode("\n", $lines);
    }
}
