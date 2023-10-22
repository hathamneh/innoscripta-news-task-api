<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepository $repository,
    )
    {
    }

    public function index(Request $request)
    {
        $showTop = $request->query('top', false);
        $pageSize = $request->query('pageSize', 10);

        if ($showTop) {
            return $this->repository->topCategories()->simplePaginate($pageSize);
        }
        return $this->repository->query()->simplePaginate($pageSize);
    }

    public function lookup()
    {
        return $this->repository->lookup();
    }
}
