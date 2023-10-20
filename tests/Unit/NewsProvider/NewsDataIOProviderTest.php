<?php

namespace Tests\Unit\NewsProvider;

use App\Services\NewsProviders\NewsDataIOProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsDataIOProviderTest extends TestCase
{
    protected NewsDataIOProvider $instance;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(NewsDataIOFakeApiResponses::defaultResponse());
        $this->instance = new NewsDataIOProvider();
    }

    public function testFetchArticles(): void
    {
        $params = ['country' => ['us', 'gb']];
        foreach ($this->instance->articles($params) as $articles) {
            $this->assertInstanceOf(Collection::class, $articles);
            $this->assertCount(50, $articles);
            $articleData = $articles->first();
            $this->assertEquals('Amsterdam erhöht Touristensteuer: Städtetrips werden deutlich teurer', $articleData['title']);
            $this->assertEquals('https://www.rnd.de/reise/amsterdam-erhoeht-touristensteuer-staedtetrips-werden-deutlich-teurer-H5TKTAAOFNCTVOKSTTBUTATIW4.html', $articleData['url']);
            $this->assertEquals('https://www.rnd.de/resizer/XnRysHUHUrUx4s6PMeNCS0Mdhyg=/596x0/filters:quality(70)/cloudfront-eu-central-1.images.arcpublishing.com/madsack/ORZCOM6C4VHWFPK3JWEDAI2NME.jpg', $articleData['image']);
            $this->assertEquals('2023-10-17 13:29:31', $articleData['published_at']);
            $this->assertEquals('Fritz Edelhoff', $articleData['author']);
            $this->assertEquals('germany', $articleData['country']);
            $this->assertEquals('tourism', $articleData['category']);
            $this->assertEquals('german', $articleData['language']);
            $this->assertEquals('rnd', $articleData['provider_source_id']);
            $this->assertEquals('NewsDataIOProvider', $articleData['provider']);

        }
    }
}
