<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Services\NewsProviders\NewsAPIProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    public function index(NewsAPIProvider $newsAPIProvider): JsonResponse
    {
        $news = $newsAPIProvider->fetch();

        Log::log('info', 'News fetched from NewsAPIProvider', [
            'news' => $news,
        ]);

        return response()->json([
            'news' => $news,
        ]);
    }
}
