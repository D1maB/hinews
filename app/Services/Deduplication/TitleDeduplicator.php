<?php

namespace App\Services\Deduplication;

class TitleDeduplicator
{
    /** @param array<int, array<string,mixed>> $items */
    public function deduplicate(array $items): array
    {
        $seen = [];

        return array_values(array_filter($items, function (array $item) use (&$seen): bool {
            $title = trim((string) ($item['title'] ?? ''));

            if ($title === '' || in_array($title, $seen, true)) {
                return false;
            }

            $seen[] = $title;

            return true;
        }));
    }
}
