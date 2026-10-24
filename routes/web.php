<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/news', function () {
    $feedName = "Tom's Hardware";

    if (config('app.use_local_feeds', false)) {
        $localFile = config('feeds.local')[$feedName] ?? 'toms-hardware.xml';
        $xml = @file_get_contents(storage_path('app/feeds/'.$localFile));

        if ($xml === false) {
            abort(500, 'Unable to read local Tom\'s Hardware feed.');
        }
    } else {
        $remoteUrl = config('feeds')[$feedName] ?? 'https://www.tomshardware.com/feeds.xml';
        $response = Http::timeout(15)->get($remoteUrl);

        if (! $response->successful()) {
            abort(502, 'Unable to fetch Tom\'s Hardware feed.');
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
            'category' => trim((string) $item->category),
            'author' => trim((string) ($item->author ?? $item->children($namespaces['dc'] ?? [])->creator ?? '')),
            'image' => $image ?: null,
        ];
    }

    return response()->json($news);
});
