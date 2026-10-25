<?php

namespace Tests\Unit;

use App\Services\ComputerBaseFeedParser;
use App\Services\PcWorldFeedParser;
use App\Services\TechPowerUpFeedParser;
use App\Services\TechSpotFeedParser;
use App\Services\TomsHardwareFeedParser;
use App\Services\WccftechFeedParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FeedParsersTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string}>
     */
    public static function parsers(): array
    {
        return [
            'Tom\'s Hardware' => [TomsHardwareFeedParser::class],
            'PCWorld' => [PcWorldFeedParser::class],
            'TechPowerUp' => [TechPowerUpFeedParser::class],
            'TechSpot' => [TechSpotFeedParser::class],
            'Wccftech' => [WccftechFeedParser::class],
            'ComputerBase' => [ComputerBaseFeedParser::class],
        ];
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
