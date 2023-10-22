<?php

namespace App\Http\Controllers;

use App\Repositories\CountryRepository;
use Illuminate\Support\Collection;

class CountryController extends Controller
{
    public function __construct(protected CountryRepository $repository)
    {
    }

    public function lookup(): Collection
    {
        return $this->repository->lookup();
    }
}
