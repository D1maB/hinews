<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DownloadFeedsCommandTest extends TestCase
{
    public function test_command_downloads_all_feeds(): void
    {
        $xml = '<?xml version="1.0"?><rss version="2.0"><channel><title>Feed</title><item><title>Item</title><link>https://example.com/item</link></item></channel></rss>';

        Http::fake([
            '*' => Http::response($xml, 200),
        ]);

        $this->artisan('feeds:download')
            ->assertExitCode(0);

        $this->assertFileExists(storage_path('app/feeds/toms-hardware.xml'));
    }
}
