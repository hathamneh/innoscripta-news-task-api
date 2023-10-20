<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = $this->countries();
        $countries = array_map(function ($country) {
            return ['code' => $country];
        }, $countries);
        DB::table('countries')->insert($countries);
    }

    protected function countries(): array
    {

        return [
            'ae',
            'ar',
            'at',
            'au',
            'be',
            'bg',
            'br',
            'ca',
            'ch',
            'cn',
            'co',
            'cu',
            'cz',
            'de',
            'eg',
            'fr',
            'gb',
            'gr',
            'hk',
            'hu',
            'id',
            'ie',
            'il',
            'in',
            'it',
            'jp',
            'kr',
            'lt',
            'lv',
            'ma',
            'mx',
            'my',
            'ng',
            'nl',
            'no',
            'nz',
            'ph',
            'pl',
            'pt',
            'ro',
            'rs',
            'ru',
            'sa',
            'se',
            'sg',
            'si',
            'sk',
            'th',
            'tr',
            'tw',
            'ua',
            'us',
            've',
            'za'
        ];
    }
}
