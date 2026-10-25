<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostInterestScorer;
use Illuminate\Console\Command;

class ScorePostsCommand extends Command
{
    protected $signature = 'posts:score {--limit=50 : Maximum number of posts to score} {--all : Score every unscored post}';

    protected $description = 'Score posts by interest for PC users using the configured local AI model.';

    public function handle(PostInterestScorer $scorer): int
    {
        $limit = (int) $this->option('limit');

        if ($this->option('all')) {
            $limit = 0;
        }

        $query = Post::query()->whereNull('interest_score');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $posts = $query->get();
        $scored = 0;

        foreach ($posts as $post) {
            $score = $scorer->score((string) $post->title);

            if ($score === null) {
                $this->warn("  Could not score: {$post->title}");

                continue;
            }

            $post->update(['interest_score' => $score]);
            $scored++;

            $this->line("  {$score}: {$post->title}");
        }

        $this->info("Scored {$scored} post(s).");

        return self::SUCCESS;
    }
}
