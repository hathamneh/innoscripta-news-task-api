<?php

namespace App\Services\NewsProviders;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class NewsProvider
{
    protected string $baseUrl;

    protected string $providerName;

    protected array $headers = [];

    protected function http(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => $this->baseUrl,
        ])->withHeaders($this->headers);
    }

    abstract public function fetch();

    abstract public function sources(): Collection;

    public function getProviderName(): string
    {
        return $this->providerName;
    }
}

