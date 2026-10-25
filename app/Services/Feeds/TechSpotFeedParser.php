<?php

namespace App\Services\Feeds;

class TechSpotFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'TechSpot';
    }
}
