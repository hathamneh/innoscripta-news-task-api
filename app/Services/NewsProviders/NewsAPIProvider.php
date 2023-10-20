<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewsAPIProvider extends NewsProvider
{
    const CHUNK_SIZE = 10;

    protected array $articleMapping = [
        'source_id' => 'source.id',
        'source_name' => 'source.name',
        'image' => 'urlToImage',
        'published_at' => 'publishedAt',
    ];

    protected array $sourceMapping = [
        'id_from_provider' => 'id',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.newsapi.base_url');
        $this->headers = [
            'X-Api-Key' => config('services.newsapi.api_key'),
        ];
    }

    public function articles(array $params = []): \Generator
    {
        ['sources' => $sources] = $params;

        $chunks = collect($sources)->chunk(self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            yield $this->fetchArticles($chunk);
        }
    }

    public function sources(array $params = []): Collection
    {
        $results = $this->http()->get('/sources')->json();

        if ($results['status'] != 'ok') {
            Log::error($results);
            throw new \Exception($results['message'] ?? 'Error fetching sources');
        }

        return collect($results['sources'])->map(function ($source) {
            return $this->toSourceData($source);
        });
    }

    protected function fetchArticles(Collection $sources): Collection
    {
        $queryParams = [
            'sources' => $sources->implode(','),
        ];

        $results = $this->http()->get('/everything', $queryParams)->json();

        if ($results['status'] != 'ok') {
            Log::error($results);
            throw new \Exception($results['message'] ?? 'Error fetching articles');
        }

        return collect($results['articles'])->map(function ($article) {
            if ($article['source']['name'] == "[Removed]")
                return null;
            return $this->toArticleData($article);
        })->filter();
    }
}
