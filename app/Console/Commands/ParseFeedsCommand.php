<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feeds:parse')]
#[Description('Parse all configured RSS feeds.')]
class ParseFeedsCommand extends Command
{
    public function handle(): int
    {
        $failed = 0;
        $total = 0;
        $createdTotal = 0;
        $skippedTotal = 0;

        foreach (config('feeds', []) as $name => $feedConfig) {
            if (! is_array($feedConfig)) {
                $this->warn("No feed configuration for {$name}.");
                $failed++;

                continue;
            }

            $class = $feedConfig['parser'] ?? null;

            if (! is_string($class) || ! class_exists($class)) {
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
