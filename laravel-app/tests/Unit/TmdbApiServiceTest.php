<?php

namespace Tests\Unit;

use App\Services\TmdbApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TmdbApiServiceTest extends TestCase
{
    #[Test]
    public function it_returns_first_movie_match_by_title()
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [['id' => 1, 'title' => 'Test Movie']]
            ])
        ]);

        $service = new TmdbApiService();
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
                'results' => []
            ])
        ]);

        $service = new TmdbApiService();
        $result = $service->searchMovieFirstMatchByTitle('Unknown');

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_imdb_id_from_tmdb_id()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => 'tt1234567'
            ])
        ]);

        $service = new TmdbApiService();
        $imdbId = $service->fetchImdbIdFromTmdbId(123);

        $this->assertEquals('tt1234567', $imdbId);
    }

    #[Test]
    public function it_returns_null_if_no_imdb_id()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => null
            ])
        ]);

        $service = new TmdbApiService();
        $imdbId = $service->fetchImdbIdFromTmdbId(123);

        $this->assertNull($imdbId);
    }
}
