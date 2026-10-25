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

        if (isset($feed->channel)) {
            $entries = $feed->channel->item;
        } elseif (isset($feed->entry)) {
            $entries = $feed->entry;
        } else {
            $entries = [];
        }

        foreach ($entries as $item) {
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

            $title = trim((string) $item->title);

            if (isset($item->link['href'])) {
                $link = trim((string) $item->link['href']);
            } else {
                $link = trim((string) $item->link);
            }

            if (isset($item->description)) {
                $description = (string) $item->description;
            } elseif (isset($item->summary)) {
                $description = (string) $item->summary;
            } elseif (isset($item->content)) {
                $description = (string) $item->content;
            } else {
                $description = '';
            }

            if (isset($item->pubDate)) {
                $pubDate = (string) $item->pubDate;
            } elseif (isset($item->published)) {
                $pubDate = (string) $item->published;
            } elseif (isset($item->updated)) {
                $pubDate = (string) $item->updated;
            } else {
                $pubDate = '';
            }

            if (! $image && $description !== '') {
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
                    $image = $matches[1];
                }
            }

            $news[] = [
                'title' => $title,
                'link' => $link,
                'description' => trim(strip_tags($description)),
                'pubDate' => trim($pubDate),
                'image' => $image ?: null,
            ];
        }

        return $news;
    }
}
