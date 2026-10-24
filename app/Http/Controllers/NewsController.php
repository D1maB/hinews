<?php

namespace App\Http\Controllers;

use App\Services\TomsHardwareFeedParser;

class NewsController extends Controller
{
    public function __construct(private TomsHardwareFeedParser $feedParser) {}

    public function index()
    {
        $news = $this->feedParser->parseFirst();

        return response()->json($news);
    }
}
