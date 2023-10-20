<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Country;
use App\Models\NewsSource;
use App\Repositories\ArticleRepository;
use App\Services\NewsProviders\NewsProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FetchNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-news {provider?} {--sources=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from all providers';

    protected NewsProvider $newsProvider;

    public function __construct(protected ArticleRepository $articleRepository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(NewsProvider ...$newsProviders): void
    {
        $this->scrapeNews(...$newsProviders);
    }

    protected function scrapeNews(NewsProvider ...$newsProviders): void
    {
        $this->info('Fetching news from all providers...');

        foreach ($newsProviders as $newsProvider) {
            try {
                $this->setCurrentProvider($newsProvider);
                $this->fetchFromProvider();
            } catch (\Exception $e) {
                Log::error($e);
                $this->error($e->getMessage());
            }
        }

        $this->info('Fetching News completed!');
    }

    protected function fetchFromProvider(): void
    {
        $this->info("\nFetching news from {$this->newsProvider->name()}...");

        $countries = Country::all()->pluck('code')->values()->toArray();

        $sources = $this->fetchSources(['country' => $countries]);

        $totalArticlesCreated = $this->fetchArticles($countries, $sources);

        $this->info("\n{$totalArticlesCreated} Articles saved from {$this->newsProvider->name()} successfully!\n");
    }

    protected function fetchSources(array $params): Collection
    {
        $sourcesData = $this->newsProvider->sources($params);
        $this->updateCategories($sourcesData);
        return $this->saveSources($sourcesData);
    }

    protected function fetchArticles(array $countries, Collection $sources): int
    {
        $sourcesIds = $sources->pluck('id_from_provider')->values()->toArray();
        $params = ['country' => $countries, 'sources' => $sourcesIds];

        $bar = $this->output->createProgressBar();
        $totalArticlesCreated = 0;
        foreach ($this->newsProvider->articles($params) as $articlesDataChunk) {
            $this->updateCategories($articlesDataChunk);
            $totalArticlesCreated += $this->saveArticles($articlesDataChunk, $sources);
            $bar->advance($articlesDataChunk->count());
        }

        $bar->finish();

        return $totalArticlesCreated;
    }

    protected function updateCategories(Collection $items): void
    {
        $categories = $items->map(function ($item) {
            if (!isset($item['category'])) {
                return null;
            }

            return [
                'name' => $item['category'],
            ];
        })->filter()->unique('name')->toArray();

        Category::upsert($categories, ['name']);
    }

    protected function saveArticles(Collection $articlesData, Collection $loadedSources): int
    {
        $data = $articlesData->map(function ($article) use ($loadedSources) {
            $source = $this->findSourceByProviderSourceId(
                $article['provider_source_id'],
                $loadedSources,
            );

            if (is_null($source)) {
                return null;
            }

            // replace source id from provider with source id from database
            $article['source_id'] = $source->id;
            unset($article['provider_source_id']);
            return $article;
        })->filter()->toArray();

        return $this->articleRepository->upsert($data);
    }

    protected function saveSources(Collection $sourcesData): Collection
    {
        return NewsSource::getOrCreateMany($sourcesData);
    }

    protected function findSourceByProviderSourceId(
        ?string    $providerSourceId,
        Collection $loadedSources,
    ): ?NewsSource
    {
        if (is_null($providerSourceId)) {
            return null;
        }
        $source = $loadedSources->where('id_from_provider', $providerSourceId)->first();
        // only access database if source is not found in loaded sources
        // this should not cause n+1
        if (is_null($source)) {
            $source = NewsSource::firstOrCreate([
                'provider' => $this->newsProvider->name(),
                'id_from_provider' => $providerSourceId,
            ], [
                'name' => $providerSourceId,
            ]);
        }
        return $source;
    }

    protected function setCurrentProvider(NewsProvider $newsProvider): void
    {
        $this->newsProvider = $newsProvider;
    }
}
