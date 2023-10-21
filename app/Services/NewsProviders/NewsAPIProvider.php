<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewsAPIProvider extends NewsProvider
{
    const CHUNK_SIZE = 10;

    const SECONDS_BETWEEN_REQUESTS = 1;

    protected array $articleMapping = [
        'provider_source_id' => ['source.id', 'source.name'],
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

        $this->articleMapping['category'] = fn($articleData) => $this->splitValue($articleData['category']);
        $this->articleMapping['country'] = fn($articleData) => $this->splitValue($articleData['country']);

        $this->sourceMapping['category'] = fn($sourceData) => $this->splitValue($sourceData['category']);
        $this->sourceMapping['country'] = fn($sourceData) => $this->splitValue($sourceData['country']);
    }

    public function articles(array $params = []): \Generator
    {
        ['sources' => $sources, 'from' => $from] = $params;

        $chunks = collect($sources)->chunk(self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            yield $this->fetchArticles($chunk, $from);
            sleep(self::SECONDS_BETWEEN_REQUESTS);
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

    protected function fetchArticles(Collection $sources, ?string $from): Collection
    {
        $queryParams = [
            'sources' => $sources->implode(','),
            'from' => $from,
        ];

        $results = $this->http()->get('/top-headlines', $queryParams)->json();

        if ($results['status'] != 'ok') {
            Log::error($results);
            throw new \Exception($results['message'] ?? 'Error fetching articles');
        }

        return collect($results['articles'])->map(function ($article) {
            if ($article['source']['name'] == "[Removed]")
                return null;
            return $this->toArticle($article);
        })->filter();
    }

    protected function splitValue($value, $separator = ',')
    {
        return collect(explode($separator, $value))->map(function ($item) {
            return trim($item);
        })->filter();
    }
}
