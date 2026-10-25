<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Scoring\PostInterestScorer;
use Illuminate\Console\Command;

class ScorePostsCommand extends Command
{
    protected $signature = 'posts:score {id? : ID of a specific post to score} {--limit=50 : Maximum number of posts to score} {--all : Score every unscored post}';

    protected $description = 'Score posts by interest for PC users using the configured local AI model.';

    public function handle(PostInterestScorer $scorer): int
    {
        $id = $this->argument('id');

        if ($id !== null) {
            $post = Post::find($id);

            if ($post === null) {
                $this->error("Post not found: {$id}");

                return self::FAILURE;
            }

            $score = $scorer->score((string) $post->title);

            if ($score === null) {
                $this->warn("  Could not score: {$post->title}");

                return self::FAILURE;
            }

            $post->update(['interest_score' => $score]);
            $this->line("  {$score}: {$post->title}");

            return self::SUCCESS;
        }

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
