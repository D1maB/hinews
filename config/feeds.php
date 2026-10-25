<?php

use App\Services\PcWorldFeedParser;
use App\Services\TechPowerUpFeedParser;
use App\Services\TechSpotFeedParser;
use App\Services\TomsHardwareFeedParser;
use App\Services\WccftechFeedParser;

return [
    'Wccftech' => [
        'parser' => WccftechFeedParser::class,
        'remote' => 'https://wccftech.com/feed/',
        'local' => 'wccftech.xml',
    ],
    "Tom's Hardware" => [
        'parser' => TomsHardwareFeedParser::class,
        'remote' => 'https://www.tomshardware.com/feeds.xml',
        'local' => 'toms-hardware.xml',
    ],
    'PCWorld' => [
        'parser' => PcWorldFeedParser::class,
        'remote' => 'https://www.pcworld.com/feed',
        'local' => 'pcworld.xml',
    ],
    'TechPowerUp' => [
        'parser' => TechPowerUpFeedParser::class,
        'remote' => 'https://www.techpowerup.com/rss/news',
        'local' => 'techpowerup.xml',
    ],
    'TechSpot' => [
        'parser' => TechSpotFeedParser::class,
        'remote' => 'https://www.techspot.com/backend.xml',
        'local' => 'techspot.xml',
    ],
];
