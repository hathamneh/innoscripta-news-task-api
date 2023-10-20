<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewsDataIOProvider extends NewsProvider
{
    const CHUNK_SIZE = 5;
    const SECONDS_BETWEEN_REQUESTS = 2;

    protected array $articleMapping = [
        'image' => 'image_url',
        'published_at' => 'pubDate',
        'author' => 'creator',
        'url' => 'link',
    ];

    protected array $sourceMapping = [
        'id_from_provider' => 'id',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.newsdataio.base_url');
        $this->headers = [
            'X-ACCESS-KEY' => config('services.newsdataio.api_key'),
        ];
    }

    public function articles($params = []): \Generator
    {
        ['country' => $allCountries] = $params;

        $chunks = collect($allCountries)->chunk(self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            yield $this->fetchArticles($chunk);
            sleep(self::SECONDS_BETWEEN_REQUESTS);
        }
    }

    public function sources(array $params = []): Collection
    {
        $results = $this->http()->get('/sources')->json();

        if ($results['status'] != 'success') {
            Log::log('error', $results);
            throw new \Exception($results['results']['message'] ?? 'Error fetching articles');
        }

        return collect($results['results'])->map(function ($source) {
            return $this->toSourceData($source);
        });
    }

    protected function fetchArticles(Collection $countries): Collection
    {
        $queryParams = [
            'country' => $countries->implode(','),
        ];

        $results = $this->http()->get('/news', $queryParams)->json();

        if ($results['status'] != 'success') {
            Log::log('error', $results);
            throw new \Exception($results['results']['message'] ?? 'Error fetching articles');
        }

        return collect($results['results'])->map(function ($article) {
            return $this->toArticleData($article);
        });
    }

    protected function articleFieldValue(array $articleData, string $fieldName, $fallbackFieldName = null)
    {
        $value = parent::articleFieldValue($articleData, $fieldName, $fallbackFieldName);
        if (is_array($value)) {
            return implode(',', $value);
        }
        return $value;
    }

    protected function sourceFieldValue(array $sourceData, string $fieldName)
    {
        $value = parent::sourceFieldValue($sourceData, $fieldName);
        if (is_array($value)) {
            return implode(',', $value);
        }
        return $value;
    }
}
