<?php

namespace Tests\Feature;

use Tests\TestCase;

class NewsTest extends TestCase
{
    public function test_news_returns_parsed_feed_items(): void
    {
        $response = $this->get('/news');

        $response->assertOk();

        $data = $response->json();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertLessThanOrEqual(10, count($data));

        $first = $data[0];

        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('link', $first);
        $this->assertArrayHasKey('description', $first);
        $this->assertArrayHasKey('pubDate', $first);
        $this->assertArrayHasKey('image', $first);
        $this->assertArrayNotHasKey('category', $first);
        $this->assertArrayNotHasKey('author', $first);
    }
}
