<?php

namespace App\Services\Social;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XPoster
{
    use FormatsSocialPost;

    public function __construct(
        protected ?string $bearerToken = null,
        protected ?string $endpoint = null,
    ) {
        $this->bearerToken ??= (string) config('services.x.bearer_token');
        $this->endpoint ??= (string) config('services.x.endpoint', 'https://api.twitter.com/2/tweets');
    }

    public function post(Post $post): ?string
    {
        if (blank($this->bearerToken)) {
            Log::error('X posting skipped: missing bearer token.');

            return null;
        }

        $response = Http::retry(3, 200, throw: false)
            ->withToken((string) $this->bearerToken)
            ->post($this->endpoint, [
                'text' => $this->textFor($post),
            ]);

        if (! $response->successful()) {
            Log::error('X posting failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $id = $response->json('data.id');

        if (! is_scalar($id)) {
            Log::error('X posting failed: missing post id in response.', [
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
}
