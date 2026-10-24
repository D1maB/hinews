<?php

namespace App\Services;

class TomsHardwareFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return "Tom's Hardware";
    }
}
