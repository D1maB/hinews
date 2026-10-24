<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ComputerBaseFeedParser;
use App\Services\PcPerspectiveFeedParser;
use App\Services\PcWorldFeedParser;
use App\Services\PhoronixFeedParser;
use App\Services\ServeTheHomeFeedParser;
use App\Services\TechgageFeedParser;
use App\Services\TechPowerUpFeedParser;
use App\Services\TechSpotFeedParser;
use App\Services\TomsHardwareFeedParser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feeds:parse')]
#[Description('Parse all configured RSS feeds.')]
class ParseFeedsCommand extends Command
{
    /**
     * @var array<string, string>
     */
    private array $parsers = [
        "Tom's Hardware" => TomsHardwareFeedParser::class,
        'PCWorld' => PcWorldFeedParser::class,
        'TechPowerUp' => TechPowerUpFeedParser::class,
        'ServeTheHome' => ServeTheHomeFeedParser::class,
        'Phoronix' => PhoronixFeedParser::class,
        'TechSpot' => TechSpotFeedParser::class,
        'ComputerBase' => ComputerBaseFeedParser::class,
        'Techgage' => TechgageFeedParser::class,
        'PC Perspective' => PcPerspectiveFeedParser::class,
    ];

    public function handle(): int
    {
        $failed = 0;
        $total = 0;
        $createdTotal = 0;
        $skippedTotal = 0;

        foreach (array_keys(config('feeds', [])) as $name) {
            $class = $this->parsers[$name] ?? null;

            if ($class === null) {
                $this->warn("No parser registered for {$name}.");
                $failed++;

                continue;
            }

            try {
                $items = app($class)->parse();
                $count = count($items);
                $total += $count;

                $this->info("{$name}: {$count} item(s)");

                foreach ($items as $item) {
                    $this->line('  - '.($item['title'] ?? 'Untitled'));

                    $link = $item['link'] ?? null;

                    if (! $link) {
                        continue;
                    }

                    $post = Post::firstOrCreate(
                        ['link' => $link],
                        [
                            'title' => $item['title'] ?? 'Untitled',
                            'description' => $item['description'] ?? null,
                            'pub_date' => $item['pubDate'] ?? null,
                            'image' => $item['image'] ?? null,
                            'source' => $name,
                        ]
                    );

                    if ($post->wasRecentlyCreated) {
                        $createdTotal++;
                    } else {
                        $skippedTotal++;
                    }
                }
            } catch (\Throwable $e) {
                $this->error("{$name}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Parsed {$total} item(s) in total.");
        $this->info("Saved {$createdTotal} new post(s); skipped {$skippedTotal} duplicate(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
