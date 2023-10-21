<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArticleRepository
{
    public function upsert(array $articles): int
    {
        return Article::upsert(
            $articles,
            ['url'],
        );
    }

    public function createManyFromProviderArticlesData(array $providerArticles): Collection
    {
        $relationsData = [];
        $upsertData = collect($providerArticles)->map(function (array $articleData) use (&$relationsData) {
            $relationsData[$articleData['url']] = [
                'category' => $articleData['category'] ?? [],
                'country' => $articleData['country'] ?? [],
            ];

            unset($articleData['category']);
            unset($articleData['country']);

            return $articleData;
        });

        $articles = collect();
        DB::transaction(function () use ($upsertData, $relationsData, &$articles) {
            $this->upsert($upsertData->toArray());
            $articles = $this->whereUrlIn(array_keys($relationsData));

            $articles->each(function (Article $article) use ($relationsData) {
                $article->categories()->attach($relationsData[$article->url]['category']);
                $article->countries()->attach($relationsData[$article->url]['country']);
            });
        });

        return $articles;
    }

    public function paginate(int $perPage = 10)
    {
        return Article::simplePaginate($perPage);
    }

    public function getLastFetchedArticleDate(string $provider = null): ?string
    {
        if ($provider) {
            return Article::query()->where('provider', $provider)->max('published_at');
        }
        return Article::max('published_at');
    }

    public function whereUrlIn(array $urls): Collection
    {
        return Article::query()->whereIn('url', $urls)->get();
    }
}
