<?php

namespace Tests\Integration\Movies\HttpClients;

use App\Infrastructure\Movies\HttpClients\TmdbApiClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TmdbApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    #[Test]
    public function it_returns_first_movie_match_by_title()
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [['id' => 1, 'title' => 'Test Movie']],
            ]),
        ]);

        $service = new TmdbApiClient;
        $result = $service->searchMovieFirstMatchByTitle('Test Movie');

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test Movie', $result['title']);
    }

    #[Test]
    public function it_returns_null_if_no_movie_found()
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [],
            ]),
        ]);

        $service = new TmdbApiClient;
        $result = $service->searchMovieFirstMatchByTitle('Unknown');

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_imdb_id_from_tmdb_id()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => 'tt1234567',
            ]),
        ]);

        $service = new TmdbApiClient;
        $imdbId = $service->fetchImdbIdFromTmdbId(123);

        $this->assertEquals('tt1234567', $imdbId);
    }

    #[Test]
    public function it_returns_null_if_no_imdb_id()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => null,
            ]),
        ]);

        $service = new TmdbApiClient;
        $imdbId = $service->fetchImdbIdFromTmdbId(123);

        $this->assertNull($imdbId);
    }
}
