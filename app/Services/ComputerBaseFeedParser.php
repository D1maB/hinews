<?php

namespace App\Services;

class ComputerBaseFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'ComputerBase';
    }
}
