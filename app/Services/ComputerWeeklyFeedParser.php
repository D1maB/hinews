<?php

namespace App\Services;

class ComputerWeeklyFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'ComputerWeekly';
    }
}
