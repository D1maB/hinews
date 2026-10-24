<?php

namespace Tests\Unit;

use App\Services\ComputerBaseFeedParser;
use App\Services\ComputerWeeklyFeedParser;
use App\Services\PcPerspectiveFeedParser;
use App\Services\PcWorldFeedParser;
use App\Services\PhoronixFeedParser;
use App\Services\ServeTheHomeFeedParser;
use App\Services\TechgageFeedParser;
use App\Services\TechPowerUpFeedParser;
use App\Services\TechSpotFeedParser;
use App\Services\TomsHardwareFeedParser;
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
            'ServeTheHome' => [ServeTheHomeFeedParser::class],
            'Phoronix' => [PhoronixFeedParser::class],
            'TechSpot' => [TechSpotFeedParser::class],
            'ComputerBase' => [ComputerBaseFeedParser::class],
            'Techgage' => [TechgageFeedParser::class],
            'PC Perspective' => [PcPerspectiveFeedParser::class],
            'ComputerWeekly' => [ComputerWeeklyFeedParser::class],
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
