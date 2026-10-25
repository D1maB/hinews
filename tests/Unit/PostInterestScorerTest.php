<?php

namespace Tests\Unit;

use App\Services\Scoring\PostInterestScorer;
use Tests\TestCase;

class PostInterestScorerTest extends TestCase
{
    public function test_empty_title_returns_null(): void
    {
        $this->assertNull((new PostInterestScorer())->score('   '));
    }

    public function test_missing_url_returns_null(): void
    {
        config(['ai.providers.ollama.url' => '']);

        $this->assertNull((new PostInterestScorer())->score('Example title'));
    }
}
