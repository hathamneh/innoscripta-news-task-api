<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Repositories\ArticleRepository;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(protected ArticleRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $params = $request->validate([
            'source.*' => 'string|nullable',
            'category.*' => 'string|nullable',
            'country.*' => 'string|nullable',
            'language.*' => 'string|nullable',
            'search' => 'string|nullable',
            'pageSize' => 'integer|nullable',
        ]);

        $results = $this->repository->filter($params)->simplePaginate(10);

        return ArticleResource::collection($results);
    }
}
