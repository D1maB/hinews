<?php

namespace App\Services\Feeds;

class TomsHardwareFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return "Tom's Hardware";
    }
}
