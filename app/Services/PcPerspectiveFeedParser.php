<?php

namespace App\Services;

class PcPerspectiveFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'PC Perspective';
    }
}
