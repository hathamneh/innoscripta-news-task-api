<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Models\NewsSource;

class NewsSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return NewsSource::simplePaginate(10);
    }
}
