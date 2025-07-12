<?php

namespace Tests\Integration;

use App\Services\TmdbApiService;
use Tests\TestCase;

class TmdbApiTest extends TestCase
{
    /** @test */
    public function it_can_fetch_movie_from_tmdb()
    {
        $service = new TmdbApiService();
        $result = $service->searchMovie('Inception');

        $this->assertIsArray($result);
        $this->assertEquals('Inception', $result['title']);
    }

    /** @test */
    public function it_can_fetch_imdb_id()
    {
        $service = new TmdbApiService();
        $imdb = $service->getImdbId(27205); // ID de "Inception" en TMDb

        $this->assertEquals('tt1375666', $imdb);
    }
}
