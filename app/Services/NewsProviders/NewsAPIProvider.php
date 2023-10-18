<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Collection;

class NewsAPIProvider extends NewsProvider
{
    protected string $providerName = 'NewsAPI';

    public function __construct()
    {
        $this->baseUrl = config('services.newsapi.base_url');
        $this->headers = [
            'X-Api-Key' => config('services.newsapi.api_key'),
        ];
    }

    public function fetch()
    {
        return $this->http()->get('/everything')->json();
    }

    public function sources(): Collection
    {
        $results = $this->http()->get('/sources')->json();
        return collect($results['sources'])->map(function ($source) {
            return [
                'id_from_provider' => $source['id'],
                'provider' => $this->getProviderName(),
                'name' => $source['name'],
                'description' => $source['description'],
                'url' => $source['url'],
                'category' => $source['category'],
                'language' => $source['language'],
                'country' => $source['country'],
            ];
        });
    }
}
