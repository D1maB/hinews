<?php

namespace App\Services\Embeddings;

use Laravel\Ai\Embeddings;

class EmbeddingService
{
    public function __construct(
        protected string $provider = 'ollama',
        protected string $model = 'nomic-embed-text:v1.5',
    ) {}

    /**
     * @return array<int,float>|null
     */
    public function generate(string $text): ?array
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        try {
            $response = Embeddings::for([$text])->generate(
                provider: $this->provider,
                model: $this->model,
            );

            $embedding = $response->embeddings[0] ?? null;

            return is_array($embedding) ? array_values($embedding) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) === 0 || count($a) !== count($b)) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * $b[$i];
            $normA += $value * $value;
            $normB += $b[$i] * $b[$i];
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    public function isDuplicate(array $embedding, iterable $existing, float $threshold = 0.9): bool
    {
        foreach ($existing as $item) {
            $existingEmbedding = is_array($item) ? ($item['embedding'] ?? $item) : null;

            if (! is_array($existingEmbedding)) {
                continue;
            }

            if ($this->cosineSimilarity($embedding, $existingEmbedding) >= $threshold) {
                return true;
            }
        }

        return false;
    }
}
