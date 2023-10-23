<?php

namespace App\Services\NewsProviders\Contracts;

use Illuminate\Support\Collection;

interface NewsProvider
{
    public function articles(array $params = []): \Generator;

    public function sources(array $params = []): Collection;

    public function name(): string;
}
