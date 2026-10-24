<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('feeds:download')]
#[Description('Download all configured RSS feeds into local development storage.')]
class DownloadFeedsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $feeds = config('feeds', []);
        $directory = storage_path('app/feeds');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $failed = 0;

        foreach ($feeds as $name => $feed) {
            $remote = is_array($feed) ? ($feed['remote'] ?? null) : $feed;
            $local = is_array($feed) ? ($feed['local'] ?? null) : null;

            if (! $remote || ! $local) {
                $this->warn("Skipping {$name}: missing remote or local path.");
                $failed++;

                continue;
            }

            try {
                $response = Http::timeout(30)->get($remote);

                if (! $response->successful()) {
                    throw new \RuntimeException("HTTP {$response->status()}");
                }

                file_put_contents($directory.DIRECTORY_SEPARATOR.$local, $response->body());
                $this->info("Downloaded {$name} -> {$local}");
            } catch (\Throwable $e) {
                $this->error("Failed {$name}: {$e->getMessage()}");
                $failed++;
            }
        }

        if ($failed > 0) {
            $this->warn("{$failed} feed(s) failed to download.");

            return self::FAILURE;
        }

        $this->info('All feeds downloaded successfully.');

        return self::SUCCESS;
    }
}
