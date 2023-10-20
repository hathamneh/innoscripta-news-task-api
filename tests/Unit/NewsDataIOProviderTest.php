<?php

test('fetch articles', function () {
    \Illuminate\Support\Facades\Config::set('services.newsdataio.base_url', 'https://example.com');
    \Illuminate\Support\Facades\Config::set('services.newsdataio.api_key', '1234567890');

    $newsdataioService = new \App\Services\NewsProviders\NewsDataIOProvider();
});
