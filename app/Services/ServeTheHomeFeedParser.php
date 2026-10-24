<?php

namespace App\Services;

class ServeTheHomeFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'ServeTheHome';
    }
}
