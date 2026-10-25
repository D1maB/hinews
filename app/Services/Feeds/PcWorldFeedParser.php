<?php

namespace App\Services\Feeds;

class PcWorldFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'PCWorld';
    }
}
