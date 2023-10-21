<?php

namespace App\Services\NewsProviders\Models;

class NewsProviderArticle extends NewsProviderModel
{
    protected array $attributes = [
        'provider' => '',
        'provider_source_id' => '',
        'title' => '',
        'url' => '',
        'description' => null,
        'image' => null,
        'published_at' => null,
        'content' => null,
        'author' => null,
        'category' => null,
        'country' => null,
        'language' => null,
    ];
}
