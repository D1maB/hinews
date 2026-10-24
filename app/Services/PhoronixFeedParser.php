<?php

namespace App\Services;

class PhoronixFeedParser extends FeedParser
{
    protected function feedName(): string
    {
        return 'Phoronix';
    }
}
