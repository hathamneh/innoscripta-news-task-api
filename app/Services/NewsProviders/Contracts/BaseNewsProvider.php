<?php

namespace App\Services\NewsProviders\Contracts;

use App\Services\NewsProviders\Models\NewsProviderArticle;
use App\Services\NewsProviders\Models\NewsProviderSource;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

trait BaseNewsProvider
{
    protected function http(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => $this->getBaseUrl(),
        ])->withHeaders($this->getHeaders())
            ->withQueryParameters($this->getQuery());
    }

    protected function getBaseUrl(): string
    {
        if (property_exists($this, 'baseUrl')) {
            return $this->baseUrl;
        }
        return '';
    }

    protected function getHeaders(): array
    {
        if (property_exists($this, 'headers')) {
            return $this->headers;
        }
        return [];
    }

    protected function getQuery(): array
    {
        if (property_exists($this, 'query')) {
            return $this->query;
        }
        return [];
    }

    abstract public function articles(array $params = []): \Generator;

    abstract public function sources(array $params = []): Collection;

    protected function toArticle(array $articleData): NewsProviderArticle
    {
        $article = new NewsProviderArticle();
        if (property_exists($this, 'articleMapping')) {
            $article->setMapper($this->articleMapping);
        }
        $article->fill([
            'provider' => $this->name(),
            ...$articleData
        ]);

        return $article;
    }

    protected function toSource(array $sourceData): NewsProviderSource
    {
        $source = new NewsProviderSource();
        if (property_exists($this, 'sourceMapping')) {
            $source->setMapper($this->sourceMapping);
        }
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

