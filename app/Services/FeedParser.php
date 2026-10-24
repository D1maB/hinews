<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

abstract class FeedParser
{
    abstract protected function feedName(): string;

    public function parse(): array
    {
        $name = $this->feedName();
        $feeds = config('feeds', []);
        $feedConfig = $feeds[$name] ?? null;

        if (! is_array($feedConfig)) {
            return [];
        }

        $remote = $feedConfig['remote'] ?? null;
        $local = $feedConfig['local'] ?? null;

        if (config('app.use_local_feeds', false)) {
            $fileName = $local ?? (str($name)->slug()->append('.xml')->value());

            $xml = @file_get_contents(storage_path('app/feeds/'.$fileName));

            if ($xml === false) {
                abort(500, "Unable to read local {$name} feed.");
            }
        } else {
            $response = Http::timeout(15)->get($remote);

            if (! $response->successful()) {
                abort(502, "Unable to fetch {$name} feed.");
            }

            $xml = $response->body();
        }

        $feed = simplexml_load_string($xml);

        if ($feed === false) {
            abort(500, 'Unable to parse RSS feed.');
        }

        $news = [];

        foreach ($feed->channel->item as $item) {
            if (count($news) >= 10) {
                break;
            }

            $namespaces = $item->getNamespaces(true);

            $image = null;
            if (isset($namespaces['media'])) {
                $media = $item->children($namespaces['media']);
                $image = (string) ($media->thumbnail ?? $media->content ?? '');
            }

            if (! $image && isset($namespaces['dc'])) {
                $dc = $item->children($namespaces['dc']);
                $image = (string) ($dc->source ?? '');
            }

            $news[] = [
                'title' => trim((string) $item->title),
                'link' => trim((string) $item->link),
                'description' => trim(strip_tags((string) $item->description)),
                'pubDate' => trim((string) $item->pubDate),
                'image' => $image ?: null,
            ];
        }

        return $news;
    }
}
