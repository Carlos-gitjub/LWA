<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MovieSearchTest extends TestCase
{
    #[Test]
    public function it_returns_movie_platforms_by_title()
    {
        // Fake external APIs
        Http::fake([
            'https://api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [
                    ['id' => 123, 'title' => 'Inception', 'release_date' => '2010-07-16']
                ]
            ]),
            'https://api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => 'tt1375666'
            ]),
            'https://api.watchmode.com/v1/search/*' => Http::response([
                'title_results' => [
                    ['id' => 456, 'name' => 'Inception', 'year' => 2010, 'type' => 'movie']
                ]
            ]),
            'https://api.watchmode.com/v1/title/456/sources/*' => Http::response([
                [
                    'name' => 'Netflix',
                    'type' => 'sub',
                    'format' => 'HD',
                    'price' => null,
                    'web_url' => 'https://netflix.com/inception'
                ]
            ])
        ]);

        $response = $this->postJson('/movies-streaming/search', [
            'title' => 'Inception',
            'region' => 'ES'
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'platform' => 'Netflix',
            'type' => 'sub',
            'format' => 'HD',
            'price' => 'Incluido',
            'url' => 'https://netflix.com/inception'
        ]);
    }
}
