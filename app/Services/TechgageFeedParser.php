<?php

namespace App\Services;

class TechgageFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'Techgage';
    }
}
