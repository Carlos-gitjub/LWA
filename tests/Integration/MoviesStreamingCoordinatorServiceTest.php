<?php

namespace Tests\Integration;

use App\Services\MoviesStreamingCoordinatorService;
use App\Services\TmdbApiService;
use App\Services\WatchmodeApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MoviesStreamingCoordinatorServiceTest extends TestCase
{
    #[Test]
    public function it_returns_subscription_platforms_for_movie_list()
    {
        Http::fake([
            // TMDb
            'api.themoviedb.org/3/movie/101/external_ids*' => Http::response(['imdb_id' => 'tt001']),
            // Watchmode
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 789]]
            ]),
            'api.watchmode.com/v1/title/789/sources*' => Http::response([
                ['name' => 'Disney+', 'type' => 'sub'],
                ['name' => 'Disney+', 'type' => 'sub'], // duplicate, to test `unique()`
                ['name' => 'Amazon', 'type' => 'buy']   // not 'sub', should be ignored
            ])
        ]);

        $tmdb = new TmdbApiService();
        $watchmode = new WatchmodeApiService($tmdb);
        $coordinator = new MoviesStreamingCoordinatorService($tmdb, $watchmode);

        $result = $coordinator->getSubscriptionPlatformsForList([
            ['id' => 101, 'title' => 'Test', 'year' => '2021']
        ], 'ES');

        $this->assertArrayHasKey('Disney+', $result);
        $this->assertEquals(1, $result['Disney+']['count']);
        $this->assertContains('Test (2021)', $result['Disney+']['movies']);
    }
}
