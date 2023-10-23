<?php

namespace App\Services\NewsProviders;

use App\Services\NewsProviders\Contracts\BaseNewsProvider;
use App\Services\NewsProviders\Contracts\NewsProvider;
use App\Services\NewsProviders\Models\NewsProviderSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TheGuardianProviderBase implements NewsProvider
{
    use BaseNewsProvider;

    const PAGE_SIZE = 50;
    protected array $articleMapping = [
        'title' => 'webTitle',
        'url' => 'webUrl',
        'description' => 'fields.trailText',
        'image' => 'fields.thumbnail',
        'published_at' => 'webPublicationDate',
        'author' => 'fields.byline',
        'category' => 'sectionName',
    ];

    protected string $baseUrl;

    protected array $query;

    public function __construct()
    {
        $this->baseUrl = config('services.theguardian.base_url');
        $this->query = [
            'api-key' => config('services.theguardian.api_key'),
        ];

        $this->articleMapping['provider_source_id'] = fn($articleData) => 'TheGuardian';
        $this->articleMapping['language'] = fn($articleData) => 'en';
    }

    public function articles($params = []): \Generator
    {
        yield $this->fetchArticles();
    }

    public function sources(array $params = []): Collection
    {
        return collect([
            new NewsProviderSource([
                'provider' => $this->name(),
                'id_from_provider' => 'TheGuardian',
                'name' => 'The Guardian',
                'description' => 'The Guardian is a British daily newspaper. It was founded in 1821 as The Manchester Guardian, before it changed its name in 1959. Along with its sister papers, The Observer and The Guardian Weekly, The Guardian is part of the Guardian Media Group, owned by the Scott Trust Limited.',
                'url' => 'https://www.theguardian.com/international',
                'language' => 'en',
            ])
        ]);
    }

    protected function fetchArticles(): Collection
    {
        $queryParams = [
            'page-size' => self::PAGE_SIZE,
            'show-fields' => 'thumbnail,trailText,byline',
        ];

        $res = $this->http()->get('/news', $queryParams)->json();
        $results = $res['response'];

        if ($results['status'] != 'ok') {
            Log::log('error', $results);
            throw new \Exception($results['results']['message'] ?? 'Error fetching articles');
        }

        return collect($results['results'])->map(function ($article) {
            return $this->toArticle($article);
        });
    }

}
