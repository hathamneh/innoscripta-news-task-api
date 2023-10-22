<?php

namespace App\Repositories;

use App\Models\NewsSource;
use App\Services\NewsProviders\Models\NewsProviderSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsSourceRepository
{
    public function query(): Builder
    {
        return NewsSource::query();
    }

    public function lookup(): Collection
    {
        return $this->query()->orderBy('name')->get(['id', 'name'])->map(function ($source) {
            return [
                'id' => $source->id,
                'name' => Str::ucfirst($source->name),
            ];
        });
    }

    public function find(int $id): NewsSource
    {
        return $this->query()->findOrFail($id);
    }

    public function topSources(): Builder
    {
        return $this->query()
            ->withCount('articles')
            ->orderByDesc('articles_count');
    }

    public function upsert(array $sources): int
    {
        return NewsSource::upsert(
            $sources,
            ['provider', 'id_from_provider'],
        );
    }

    public function whereIn(Collection $providerSources): Collection
    {
        $sourceIds = collect();
        $providerNames = collect();
        $providerSources->each(function (NewsProviderSource $source) use (&$sourceIds, &$providerNames) {
            $sourceIds->push($source->id_from_provider);
            $providerNames->push($source->provider);
        });
        return NewsSource::query()
            ->whereIn('id_from_provider', $sourceIds->unique()->toArray())
            ->whereIn('provider', $providerNames->unique()->toArray())
            ->get();
    }

    public function createManyFromProviderSources(Collection $providerSources): Collection
    {
        // 1. prepare the data for the upsert and the relations
        $relationsData = [];
        $upsertData = $providerSources->map(function (NewsProviderSource $source) use (&$relationsData) {
            // remove category and country from the source data
            $creationData = $source->toArray();
            unset($creationData['category']);
            unset($creationData['country']);

            // add the relation data to the relations array
            $relationsData[$source->id_from_provider] = [
                'category' => $source->category ?? [],
                'country' => $source->country ?? [],
            ];

            return $creationData;
        });

        $sources = collect();
        DB::transaction(function () use ($upsertData, $providerSources, $relationsData, &$sources) {
            // 2. upsert the sources
            $this->upsert($upsertData->toArray());
            $sources = $this->whereIn($providerSources);

            // 3. attach the relations (possible N+1 issue)
            $sources->each(function (NewsSource $source) use ($relationsData) {
                $source->categories()->attach($relationsData[$source->id_from_provider]['category']);
                $source->countries()->attach($relationsData[$source->id_from_provider]['country']);
            });
        });

        return $sources;
    }
}
