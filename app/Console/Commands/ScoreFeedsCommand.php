<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostInterestScorer;
use App\Services\TitleDeduplicator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feeds:score')]
#[Description('Parse configured RSS feeds, score posts with AI, and save posts with a score above 50.')]
class ScoreFeedsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PostInterestScorer $scorer, TitleDeduplicator $deduplicator): int
    {
        $failed = 0;
        $total = 0;
        $saved = 0;
        $skipped = 0;

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
                $items = $deduplicator->deduplicate($items);
                $count = count($items);
                $total += $count;

                $this->info("{$name}: {$count} item(s)");

                foreach ($items as $item) {
                    $this->line('  - '.($item['title'] ?? 'Untitled'));

                    $link = $item['link'] ?? null;

                    if (! $link) {
                        continue;
                    }

                    $score = $scorer->score((string) ($item['title'] ?? ''));

                    if ($score === null || $score <= 50) {
                        $this->line('    skipped (score: '.($score === null ? 'n/a' : $score).')');
                        $skipped++;

                        continue;
                    }

                    $post = Post::updateOrCreate(
                        ['link' => $link],
                        [
                            'title' => $item['title'] ?? 'Untitled',
                            'description' => $item['description'] ?? null,
                            'pub_date' => $item['pubDate'] ?? null,
                            'image' => $item['image'] ?? null,
                            'source' => $name,
                            'interest_score' => $score,
                        ]
                    );

                    $this->line("    saved (score: {$score})");
                    $saved++;
                }
            } catch (\Throwable $e) {
                $this->error("{$name}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Parsed {$total} item(s) in total.");
        $this->info("Saved {$saved} post(s); skipped {$skipped} (score <= 50 or unscored).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
