<?php

namespace App\Services\NewsProviders\Contracts;

use App\Services\NewsProviders\Models\NewsProviderArticle;
use App\Services\NewsProviders\Models\NewsProviderSource;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class BaseNewsProvider implements NewsProvider
{
    protected string $baseUrl;

    protected array $headers = [];

    protected array $query = [];

    protected array $articleMapping = [];

    protected array $sourceMapping = [];

    protected function http(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => $this->baseUrl,
        ])->withHeaders($this->headers)
            ->withQueryParameters($this->query);
    }

    abstract public function articles(array $params = []): \Generator;

    abstract public function sources(array $params = []): Collection;

    protected function toArticle(array $articleData): NewsProviderArticle
    {
        $article = new NewsProviderArticle();
        $article->setMapper($this->articleMapping);
        $article->fill([
            'provider' => $this->name(),
            ...$articleData
        ]);

        return $article;
    }

    protected function toSourceData(array $sourceData): NewsProviderSource
    {
        $source = new NewsProviderSource();
        $source->setMapper($this->sourceMapping);
        $source->fill([
            'provider' => $this->name(),
            ...$sourceData
        ]);

        return $source;
    }


    public function name(): string
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }
}

