<?php

namespace App\Services\NewsProviders\Models;

class NewsProviderSource extends NewsProviderModel
{
    protected array $attributes = [
        'provider' => '',
        'id_from_provider' => '',
        'name' => '',
        'description' => null,
        'url' => null,
        'category' => null,
        'language' => null,
        'country' => null,
    ];
}
