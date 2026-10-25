<?php

namespace App\Services\Feeds;

class WccftechFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'Wccftech';
    }
}
