<?php

namespace Tests\Unit;

use App\Services\Deduplication\TitleDeduplicator;
use Tests\TestCase;

class TitleDeduplicatorTest extends TestCase
{
    public function test_duplicates_are_removed_by_title(): void
    {
        $deduplicator = new TitleDeduplicator;

        $items = [
            ['title' => 'Alpha', 'link' => 'https://a'],
            ['title' => 'Beta', 'link' => 'https://b'],
            ['title' => 'Alpha', 'link' => 'https://c'],
        ];

        $result = $deduplicator->deduplicate($items);

        $this->assertCount(2, $result);
        $this->assertSame(['Alpha', 'Beta'], array_column($result, 'title'));
    }

    public function test_empty_and_blank_titles_are_skipped(): void
    {
        $deduplicator = new TitleDeduplicator;

        $items = [
            ['title' => '', 'link' => 'https://a'],
            ['title' => '   ', 'link' => 'https://b'],
            ['title' => 'Gamma', 'link' => 'https://c'],
        ];

        $result = $deduplicator->deduplicate($items);

        $this->assertCount(1, $result);
        $this->assertSame('Gamma', $result[0]['title']);
    }
}
