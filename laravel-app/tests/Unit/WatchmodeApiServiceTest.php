<?php

namespace Tests\Unit;

use App\Services\TmdbApiService;
use App\Services\WatchmodeApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WatchmodeApiServiceTest extends TestCase
{
    protected function makeService(): WatchmodeApiService
    {
        return new WatchmodeApiService(new TmdbApiService());
    }

    #[Test]
    public function it_returns_first_match_by_imdb_id()
    {
        Http::fake([
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 456, 'title' => 'Test']]
            ])
        ]);

        $service = $this->makeService();
        $result = $service->searchTitleFirstMatchByImdbId('tt123');

        $this->assertIsArray($result);
        $this->assertEquals(456, $result['id']);
    }

    #[Test]
    public function it_returns_null_if_no_watchmode_result()
    {
        Http::fake([
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => []
            ])
        ]);

        $service = $this->makeService();
        $result = $service->searchTitleFirstMatchByImdbId('tt0000000');

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_sources_by_watchmode_id()
    {
        Http::fake([
            'api.watchmode.com/v1/title/456/sources*' => Http::response([
                ['name' => 'Netflix', 'type' => 'sub']
            ])
        ]);

        $service = $this->makeService();
        $sources = $service->getStreamingSourcesByWatchmodeId(456, 'ES');

        $this->assertIsArray($sources);
        $this->assertEquals('Netflix', $sources[0]['name']);
    }

    #[Test]
    public function it_returns_subscription_platforms_by_tmdb_id()
    {
        Http::fake([
            // TMDb
            'api.themoviedb.org/3/movie/101/external_ids*' => Http::response(['imdb_id' => 'tt101']),
            // Watchmode
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 789]]
            ]),
            'api.watchmode.com/v1/title/789/sources*' => Http::response([
                ['name' => 'HBO', 'type' => 'sub'],
                ['name' => 'Amazon', 'type' => 'buy']
            ])
        ]);

        $service = $this->makeService();
        $platforms = $service->getStreamingPlatformsByTmdbId(101, 'ES');

        $this->assertEquals(['HBO'], $platforms);
    }

    #[Test]
    public function it_returns_empty_array_if_no_imdb_id()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/102/external_ids*' => Http::response(['imdb_id' => null])
        ]);

        $service = $this->makeService();
        $platforms = $service->getStreamingPlatformsByTmdbId(102, 'ES');

        $this->assertEquals([], $platforms);
    }
}
