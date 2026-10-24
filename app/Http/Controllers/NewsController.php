<?php

namespace App\Http\Controllers;

use App\Services\FeedParser;

class NewsController extends Controller
{
    public function __construct(private FeedParser $feedParser) {}

    public function index()
    {
        $news = $this->feedParser->parseFirst();

        return response()->json($news);
    }
}
