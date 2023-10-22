<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryRepository
{
    public function query(): Builder
    {
        return Category::query();
    }

    public function topCategories(): Builder
    {
        return $this->query()
            ->withCount('articles')
            ->orderByDesc('articles_count');
    }

    public function lookup(): Collection
    {
        return $this->query()->orderBy('name')->get('name')->map(function ($category) {
            return [
                'id' => $category->name,
                'name' => Str::ucfirst($category->name),
            ];
        });
    }
}
