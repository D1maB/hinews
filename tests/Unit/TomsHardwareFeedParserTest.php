<?php

namespace Tests\Unit;

use App\Services\Feeds\TomsHardwareFeedParser;
use Tests\TestCase;

class TomsHardwareFeedParserTest extends TestCase
{
    public function test_parse_returns_news_items(): void
    {
        $parser = $this->app->make(TomsHardwareFeedParser::class);

        $news = $parser->parse();

        $this->assertIsArray($news);
        $this->assertNotEmpty($news);
        $this->assertLessThanOrEqual(10, count($news));

        $item = $news[0];

        $this->assertArrayHasKey('title', $item);
        $this->assertArrayHasKey('link', $item);
        $this->assertArrayHasKey('description', $item);
        $this->assertArrayHasKey('pubDate', $item);
        $this->assertArrayHasKey('image', $item);
        $this->assertArrayNotHasKey('category', $item);
        $this->assertArrayNotHasKey('author', $item);
    }
}
