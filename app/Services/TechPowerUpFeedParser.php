<?php

namespace App\Services;

class TechPowerUpFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'TechPowerUp';
    }
}
