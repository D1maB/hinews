<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Deduplication\TitleDeduplicator;
use App\Services\Persistence\PostRepository;
use App\Services\Scoring\PostInterestScorer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feeds:score')]
#[Description('Parse all configured RSS feeds, deduplicate by title, score posts with AI, and save posts with a score above 80.')]
class ScoreFeedsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PostInterestScorer $scorer, TitleDeduplicator $deduplicator, PostRepository $posts): int
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

        // Step 2: skip items already stored in the database (by link).
        $items = array_values(array_filter($allItems, function (array $item) use ($posts): bool {
            return ! $posts->existsByLink($item['link'] ?? null);
        }));

        $this->info(count($allItems).' parsed item(s); '.count($items).' new by link.');

        // Step 3: prevent duplicates by title across all parsers.
        $items = $deduplicator->deduplicate($items);

        $this->info(count($items).' unique by title.');

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

            if ($score === null || $score <= 80) {
                $this->line('    skipped (score: '.($score === null ? 'n/a' : $score).')');
                $skipped++;

                continue;
            }

            $posts->saveScored($item, $score);

            $this->line("    saved (score: {$score})");
            $saved++;
        }

        $this->info("Saved {$saved} post(s); skipped {$skipped} (score <= 80 or unscored).");

        // Step 4: schedule new posts one per hour; overflow becomes trash.
        $newPosts = Post::query()
            ->where('status', PostStatus::New->value)
            ->orderByDesc('interest_score')
            ->orderBy('id')
            ->get();

        $lastScheduled = Post::query()
            ->whereNotNull('scheduled_at')
            ->orderByDesc('scheduled_at')
            ->first();

        $base = now('UTC')->startOfHour();

        if ($lastScheduled !== null && $lastScheduled->scheduled_at !== null && $lastScheduled->scheduled_at->isFuture()) {
            $last = $lastScheduled->scheduled_at;
            $base = $last->copy()->addMinutes(20 - ($last->minute % 20))->startOfSecond();
        }

        foreach ($newPosts as $index => $post) {
            if ($index >= 12) {
                $post->update([
                    'status' => PostStatus::Trash->value,
                    'scheduled_at' => null,
                ]);

                continue;
            }

            $post->update([
                'status' => PostStatus::Scheduled->value,
                'scheduled_at' => $base->copy()->addMinutes(20 * $index)->addMinutes(random_int(0, 19))->addSeconds(random_int(0, 59)),
            ]);
        }

        $this->info('Scheduled '.min($newPosts->count(), 12).' post(s); trashed '.max($newPosts->count() - 12, 0).' overflow post(s).');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
