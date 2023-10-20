<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Repositories\ArticleRepository;

class ArticleController extends Controller
{
    public function __construct(protected ArticleRepository $articleRepository)
    {
    }

    public function index()
    {
        return $this->articleRepository->paginate();
    }
}
