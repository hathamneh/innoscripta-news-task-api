<?php

namespace App\Repositories;

use App\Models\Country;
use App\Utils\CountriesUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CountryRepository
{
    public function query(): Builder
    {
        return Country::query();
    }

    public function lookup(): Collection
    {
        return $this->query()->orderBy('code')->get('code')->map(function ($country) {
            return [
                'id' => $country->code,
                'name' => CountriesUtils::codeToNameTitle($country->code)
            ];
        });
    }
}
