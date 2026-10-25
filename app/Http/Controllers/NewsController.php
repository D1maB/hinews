<?php

namespace App\Http\Controllers;

use App\Services\Feeds\TomsHardwareFeedParser;

class NewsController extends Controller
{
    public function __construct(private TomsHardwareFeedParser $feedParser) {}

    public function index()
    {
        $news = $this->feedParser->parse();

        return response()->json($news);
    }
}
