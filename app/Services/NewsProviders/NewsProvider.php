<?php

namespace App\Services\NewsProviders;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class NewsProvider
{
    protected string $baseUrl;

    protected array $headers = [];

    protected array $articleMapping = [];

    protected array $sourceMapping = [];

    protected function http(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => $this->baseUrl,
        ])->withHeaders($this->headers);
    }

    abstract public function articles(array $params = []): \Generator;

    abstract public function sources(array $params = []): Collection;

    protected function toArticleData(array $articleData): ?array
    {
        $sourceIdFromProvider = $this->articleFieldValue($articleData, 'source_id', 'source_name');
        return [
            'provider' => $this->name(),
            'provider_source_id' => $sourceIdFromProvider,
            'title' => $this->articleFieldValue($articleData, 'title'),
            'description' => $this->articleFieldValue($articleData, 'description'),
            'url' => $this->articleFieldValue($articleData, 'url'),
            'image' => $this->articleFieldValue($articleData, 'image'),
            'published_at' => $this->articleFieldValue($articleData, 'published_at'),
            'content' => $this->articleFieldValue($articleData, 'content'),
            'author' => $this->articleFieldValue($articleData, 'author'),
            'category' => $this->articleFieldValue($articleData, 'category'),
            'country' => $this->articleFieldValue($articleData, 'country'),
            'language' => $this->articleFieldValue($articleData, 'language'),
        ];
    }

    protected function toSourceData(array $sourceData): array
    {
        return [
            'provider' => $this->name(),
            'id_from_provider' => $this->sourceFieldValue($sourceData, 'id_from_provider'),
            'name' => $this->sourceFieldValue($sourceData, 'name'),
            'description' => $this->sourceFieldValue($sourceData, 'description'),
            'url' => $this->sourceFieldValue($sourceData, 'url'),
            'category' => $this->sourceFieldValue($sourceData, 'category'),
            'language' => $this->sourceFieldValue($sourceData, 'language'),
            'country' => $this->sourceFieldValue($sourceData, 'country'),
        ];
    }

    protected function articleFieldValue(array $articleData, string $fieldName, $fallbackFieldName = null)
    {
        $value = Arr::get($articleData, $this->articleFieldName($fieldName));
        if (!is_null($value)) {
            return $value;
        }
        if (!is_null($fallbackFieldName)) {
            return Arr::get($articleData, $this->articleFieldName($fallbackFieldName));
        }
        return null;
    }

    protected function articleFieldName(string $fieldName): string
    {
        return $this->articleMapping[$fieldName] ?? $fieldName;
    }

    protected function sourceFieldValue(array $sourceData, string $fieldName)
    {
        return $sourceData[$this->sourceFieldName($fieldName)] ?? null;
    }

    protected function sourceFieldName(string $fieldName): string
    {
        return $this->sourceMapping[$fieldName] ?? $fieldName;
    }

    public function name(): string
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }
}

