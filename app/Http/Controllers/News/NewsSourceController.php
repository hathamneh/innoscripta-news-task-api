<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsSourceResource;
use App\Repositories\NewsSourceRepository;
use Illuminate\Http\Request;

class NewsSourceController extends Controller
{
    public function __construct(protected NewsSourceRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $showTop = $request->query('top', false);
        $pageSize = $request->query('pageSize', 10);

        if ($showTop) {
            return $this->repository->topSources()->simplePaginate($pageSize);
        }
        return $this->repository->query()->simplePaginate($pageSize);
    }

    public function lookup()
    {
        return $this->repository->lookup();
    }

    public function show(int $id)
    {
        $source = $this->repository->find($id);

        return new NewsSourceResource($source);
    }
}
