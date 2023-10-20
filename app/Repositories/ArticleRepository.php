<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepository
{
    public function upsert(array $articles): int
    {
        return Article::upsert(
            $articles,
            ['url'],
        );
    }

    public function paginate(int $perPage = 10)
    {
        return Article::simplePaginate($perPage);
    }
}
