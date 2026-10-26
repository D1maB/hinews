<?php

namespace App\Services\Deduplication;

use App\Services\Embeddings\EmbeddingService;

class TitleDeduplicator
{
    public function __construct(protected ?EmbeddingService $embeddings = null)
    {
        $this->embeddings ??= app()->bound(EmbeddingService::class)
            ? app(EmbeddingService::class)
            : null;
    }

    /** @param array<int, array<string,mixed>> $items */
    public function deduplicate(array $items): array
    {
        $kept = [];
        $seenTitles = [];
        $keptEmbeddings = [];

        foreach ($items as $item) {
            $title = trim((string) ($item['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            if (in_array($title, $seenTitles, true)) {
                continue;
            }

            $embedding = null;

            if ($this->embeddings !== null) {
                $embedding = $this->embeddings->generate($title);

                if ($embedding !== null && $this->embeddings->isDuplicate($embedding, $keptEmbeddings, 0.9)) {
                    continue;
                }
            }

            $seenTitles[] = $title;
            $kept[] = $item;

            if ($embedding !== null) {
                $keptEmbeddings[] = ['embedding' => $embedding];
            }
        }

        return array_values($kept);
    }
}
