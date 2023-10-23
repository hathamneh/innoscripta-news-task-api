<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Country;
use App\Models\NewsSource;
use App\Repositories\ArticleRepository;
use App\Repositories\NewsSourceRepository;
use App\Services\NewsProviders\Contracts\NewsProvider;
use App\Services\NewsProviders\Models\NewsProviderArticle;
use App\Services\NewsProviders\Models\NewsProviderModel;
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
    protected $signature = 'app:fetch-news {provider?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from all providers';

    protected NewsProvider $newsProvider;

    public function __construct(
        protected ArticleRepository    $articleRepository,
        protected NewsSourceRepository $newsSourceRepository,
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(NewsProvider ...$newsProviders): void
    {
        $provider = $this->argument('provider');
        if ($provider) {
            $newsProviders = collect($newsProviders)->filter(function (NewsProvider $newsProvider) use ($provider) {
                return $newsProvider->name() === $provider;
            })->values()->toArray();
        }
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
        $fetchingFromDate = $this->startFetchingFromDate();
        $this->info("\n[{$this->newsProvider->name()}] Fetching news " . ($fetchingFromDate ? "from $fetchingFromDate" : "") . "...");

        $countries = Country::all()->pluck('code')->values()->toArray();
        $sources = $this->fetchSources(['country' => $countries]);

        $params = ['country' => $countries, 'from' => $fetchingFromDate];
        $this->fetchArticles($sources, $params);

        $this->info("\n[{$this->newsProvider->name()}] Articles saved successfully!\n");
    }

    protected function fetchSources(array $params): Collection
    {
        $sourcesData = $this->newsProvider->sources($params);
        $this->updateCategories($sourcesData);
        return $this->saveSources($sourcesData);
    }

    protected function fetchArticles(Collection $sources, array $params): int
    {
        $params['sources'] = $sources->pluck('id_from_provider')->values()->toArray();

        $bar = $this->output->createProgressBar();
        $articlesCreated = collect();
        foreach ($this->newsProvider->articles($params) as $articlesDataChunk) {
            $this->updateCategories($articlesDataChunk);
            $articlesCreated->merge($this->saveArticles($articlesDataChunk, $sources));
            $bar->advance($articlesDataChunk->count());
        }

        $bar->finish();

        return $articlesCreated->count();
    }

    protected function updateCategories(Collection $items): void
    {
        $categories = $items->map(function (NewsProviderModel $item) {
            return $item->category;
        })->flatten()->unique()->filter();

        $categories = $categories->map(function ($category) {
            return ['name' => $category];
        })->toArray();
        Category::upsert($categories, ['name']);
    }

    protected function saveArticles(Collection $providerArticles, Collection $loadedSources): Collection
    {
        $data = $providerArticles->map(function (NewsProviderArticle $article) use ($loadedSources) {
            $source = $this->findSourceByProviderSourceId(
                $article->provider_source_id,
                $loadedSources,
            );

            if (is_null($source)) {
                return null;
            }

            $articleData = $article->toArray();
            // replace source id from provider with source id from database
            $articleData['source_id'] = $source->id;
            unset($articleData['provider_source_id']);

            return $articleData;
        })->filter()->toArray();


        return $this->articleRepository->createManyFromProviderArticlesData($data);
    }

    protected function saveSources(Collection $sourcesData): Collection
    {
        return $this->newsSourceRepository->createManyFromProviderSources($sourcesData);
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

    protected function startFetchingFromDate(): ?string
    {
        return $this->articleRepository->getLastFetchedArticleDate($this->newsProvider->name());
    }

    protected function setCurrentProvider(NewsProvider $newsProvider): void
    {
        $this->newsProvider = $newsProvider;
    }
}
