<?php

namespace App\Services\NewsProviders;

use App\Utils\CountriesUtils;
use App\Utils\LanguagesUtils;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewsDataIOProvider extends NewsProvider
{
    const CHUNK_SIZE = 5;
    const SECONDS_BETWEEN_REQUESTS = 1;

    protected array $articleMapping = [
        'provider_source_id' => 'source_id',
        'image' => 'image_url',
        'published_at' => 'pubDate',
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

        $this->articleMapping['country'] = fn($articleData) => $this->mapCountryCodes($articleData['country']);
        $this->articleMapping['language'] = fn($articleData) => $this->mapLanguageCodes($articleData['language']);
        $this->articleMapping['author'] = fn($articleData) => $this->joinValues($articleData['creator']);

        $this->sourceMapping['country'] = fn($sourceData) => $this->mapCountryCodes($sourceData['country']);
        $this->sourceMapping['language'] = fn($sourceData) => $this->mapLanguageCodes($sourceData['language']);
    }

    public function articles($params = []): \Generator
    {
        ['country' => $allCountries, 'from' => $from] = $params;

        $chunks = collect($allCountries)->chunk(self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            yield $this->fetchArticles($chunk, $from);
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

    protected function fetchArticles(Collection $countries, ?string $from): Collection
    {
        $queryParams = [
            'country' => $countries->implode(','),
            'timeframe' => $from ? $this->calculateTimeframe($from) : '48'
        ];

        $results = $this->http()->get('/news', $queryParams)->json();

        if ($results['status'] != 'success') {
            Log::log('error', $results);
            throw new \Exception($results['results']['message'] ?? 'Error fetching articles');
        }

        return collect($results['results'])->map(function ($article) {
            return $this->toArticle($article);
        });
    }

    protected function mapCountryCodes(array $countryNames): array
    {
        return array_filter(
            array_map(fn($name) => CountriesUtils::nameToCode($name), $countryNames),
            fn($code) => $code != null && $code != ''
        );
    }

    protected function mapLanguageCodes(array|string $languageNames): string
    {
        if (is_string($languageNames)) {
            return LanguagesUtils::nameToCode($languageNames);
        }

        $codes = array_filter(array_map(function ($name) {
            return LanguagesUtils::nameToCode($name);
        }, $languageNames));
        return implode(',', $codes);
    }

    protected function joinValues($value, $separator = ',')
    {
        return collect($value)->filter()->map(fn($val) => trim($val))->implode($separator);
    }

    protected function calculateTimeframe(string $from): string
    {
        $from = Carbon::create($from);
        $timeframe = now()->diffInHours($from);
        if ($timeframe > 24) {
            $timeframe = 24;
        } elseif ($timeframe < 1) {
            $timeframe = now()->diffInMinutes($from) . 'm';
        }
        return strval($timeframe);
    }
}
