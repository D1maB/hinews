<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FeedParsersTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string}>
     */
    public static function parsers(): array
    {
        $parsers = [];

        $feeds = require __DIR__.'/../../config/feeds.php';

        foreach ($feeds as $name => $feedConfig) {
            if (is_array($feedConfig) && isset($feedConfig['parser'])) {
                $parsers[$name] = [$feedConfig['parser']];
            }
        }

        return $parsers;
    }

    #[DataProvider('parsers')]
    public function test_parser_returns_valid_items(string $parserClass): void
    {
        $parser = $this->app->make($parserClass);
        $items = $parser->parse();

        $this->assertIsArray($items);

        if ($items === []) {
            return;
        }

        $first = $items[0];

        foreach (['title', 'link', 'description', 'pubDate', 'image'] as $key) {
            $this->assertArrayHasKey($key, $first);
        }

        $this->assertLessThanOrEqual(10, count($items));
    }
}
