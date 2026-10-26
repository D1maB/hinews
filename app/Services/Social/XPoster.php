<?php

namespace App\Services\Social;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class XPoster
{
    public function __construct(
        protected ?string $bearerToken = null,
        protected ?string $endpoint = null,
    ) {
        $this->bearerToken ??= (string) config('services.x.bearer_token');
        $this->endpoint ??= (string) config('services.x.endpoint', 'https://api.twitter.com/2/tweets');
    }

    public function post(Post $post): ?string
    {
        $response = Http::withToken((string) $this->bearerToken)
            ->post($this->endpoint, [
                'text' => $this->textFor($post),
            ]);

        if (! $response->successful()) {
            return null;
        }

        $id = $response->json('data.id');

        return is_scalar($id) ? (string) $id : null;
    }

    public function publish(Post $post): ?string
    {
        return $this->post($post);
    }

    public function send(Post $post): ?string
    {
        return $this->post($post);
    }

    protected function textFor(Post $post): string
    {
        $lines = array_filter([$post->title, $post->description, $post->link]);

        return implode("\n", $lines);
    }
}
