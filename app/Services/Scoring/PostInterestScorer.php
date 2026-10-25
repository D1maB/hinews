<?php

namespace App\Services\Scoring;

use Illuminate\JsonSchema\JsonSchema;

use function Laravel\Ai\agent;

class PostInterestScorer
{
    public function score(string $title): ?int
    {
        $title = trim($title);

        if ($title === '') {
            return null;
        }

        $url = config('ai.providers.ollama.url');

        if (! filled($url)) {
            return null;
        }

        $model = (string) config('ai.providers.ollama.model', 'tinyllama:latest');

        try {
            $response = agent(
                instructions: 'You score how interesting a news title is for PC users on a scale from 1 to 100. Consider hardware, software, gaming, security, Linux, AI, and PC industry news. Return only the integer score.',
                schema: fn (JsonSchema $schema) => [
                    'score' => $schema->integer()->min(1)->max(100),
                ],
            )->prompt($title, provider: 'ollama', model: $model);

            $score = (int) ($response['score'] ?? 0);

            if ($score < 1 || $score > 100) {
                return null;
            }

            return $score;
        } catch (\Throwable) {
            return null;
        }
    }
}
