<?php

namespace App\Services;

class WccftechFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'Wccftech';
    }
}
