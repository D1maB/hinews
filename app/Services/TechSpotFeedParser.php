<?php

namespace App\Services;

class TechSpotFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'TechSpot';
    }
}
