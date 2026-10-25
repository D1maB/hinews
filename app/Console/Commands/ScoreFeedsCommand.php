<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostInterestScorer;
use App\Services\TitleDeduplicator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feeds:score')]
#[Description('Parse all configured RSS feeds, deduplicate by title, score posts with AI, and save posts with a score above 50.')]
class ScoreFeedsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PostInterestScorer $scorer, TitleDeduplicator $deduplicator): int
    {
        $failed = 0;
        $allItems = [];

        // Step 1: parse XML from all configured feeds into one merged collection.
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

                $this->info("{$name}: ".count($items).' item(s)');

                foreach ($items as $item) {
                    $item['source'] = $name;
                    $allItems[] = $item;
                }
            } catch (\Throwable $e) {
                $this->error("{$name}: {$e->getMessage()}");
                $failed++;
            }
        }

        // Step 2: prevent duplicates by title across all parsers.
        $items = $deduplicator->deduplicate($allItems);

        $this->info('Parsed '.count($allItems).' item(s) in total; '.count($items).' unique by title.');

        $saved = 0;
        $skipped = 0;

        // Step 3: score and save posts above the threshold.
        foreach ($items as $item) {
            $title = $item['title'] ?? 'Untitled';
            $this->line('  - '.$title);

            $link = $item['link'] ?? null;

            if (! $link) {
                continue;
            }

            $score = $scorer->score((string) $title);

            if ($score === null || $score <= 50) {
                $this->line('    skipped (score: '.($score === null ? 'n/a' : $score).')');
                $skipped++;

                continue;
            }

            Post::updateOrCreate(
                ['link' => $link],
                [
                    'title' => $title,
                    'description' => $item['description'] ?? null,
                    'pub_date' => $item['pubDate'] ?? null,
                    'image' => $item['image'] ?? null,
                    'source' => $item['source'] ?? null,
                    'interest_score' => $score,
                ]
            );

            $this->line("    saved (score: {$score})");
            $saved++;
        }

        $this->info("Saved {$saved} post(s); skipped {$skipped} (score <= 50 or unscored).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
