<?php

namespace Database\Seeders;

use App\Services\NewsProviders\NewsProvider;
use Illuminate\Database\Seeder;

class NewsSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(NewsProvider ...$newsProviders): void
    {
        foreach ($newsProviders as $newsProvider) {
            $newsProvider->sources()->each(function ($source) {
                \App\Models\NewsSource::updateOrCreate(
                    [
                        'provider' => $source['provider'],
                        'id_from_provider' => $source['id_from_provider'],
                    ],
                    $source
                );
            });
        }
    }
}
