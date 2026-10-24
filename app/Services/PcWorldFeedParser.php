<?php

namespace App\Services;

class PcWorldFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'PCWorld';
    }
}
