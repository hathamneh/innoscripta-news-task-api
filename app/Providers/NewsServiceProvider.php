<?php

namespace App\Providers;

use App\Services\NewsProviders\NewsAPIProvider;
use App\Services\NewsProviders\NewsDataIOProvider;
use App\Services\NewsProviders\TheGuardianProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    protected array $newsProviders = [
        NewsAPIProvider::class,
        NewsDataIOProvider::class,
        TheGuardianProvider::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\NewsProviders\NewsProvider::class,
            function (Application $app) {
                return collect($this->newsProviders)->map(function ($provider) use ($app) {
                    return $app->make($provider);
                })->toArray();
            }
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->commands([
            \App\Console\Commands\FetchNews::class,
        ]);
    }
}
