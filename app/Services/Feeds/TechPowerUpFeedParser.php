<?php

namespace App\Services\Feeds;

class TechPowerUpFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'TechPowerUp';
    }
}
