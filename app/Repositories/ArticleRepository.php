<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArticleRepository
{
    public function query(): Builder
    {
        return Article::query()->orderByDesc('published_at');
    }

    public function filter(array $params)
    {
        return $this->query()
            ->when($params['source'] ?? null, function (Builder $query, $source) {
                $query->whereIn('source_id', $source);
            })
            ->when($params['category'] ?? null, function (Builder $query, $category) {
                $query->whereHas('categories', function (Builder $query) use ($category) {
                    $query->whereIn('categories.name', $category);
                });
            })
            ->when($params['country'] ?? null, function (Builder $query, $country) {
                $query->whereHas('source', function (Builder $query) use ($country) {
                    $query->whereIn('country', $country);
                });
            })
            ->when($params['language'] ?? null, function (Builder $query, $languages) {
                $query->where(function (Builder $query) use ($languages) {
                    foreach ($languages as $language) {
                        $query->orWhere('language', 'like', '%' . $language . '%')
                            ->orWhereHas('source', function (Builder $query) use ($language) {
                                $query->where('language', 'like', '%' . $language . '%');
                            });
                    }
                });
            })
            ->when($params['search'] ?? null, function (Builder $query, $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'ilike', '%' . $search . '%')
                        ->orWhere('description', 'ilike', '%' . $search . '%');
                });
            })
            ->with([
                'categories',
                'source.categories',
            ]);
    }


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
