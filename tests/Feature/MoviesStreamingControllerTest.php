<?php

namespace Tests\Feature;

use App\Http\Controllers\MoviesStreamingController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MoviesStreamingControllerTest extends TestCase
{
    #[Test]
    public function it_returns_streaming_platforms_for_title()
    {
        // Definir ruta temporal para el test
        Route::post('/testing/search-streaming', [MoviesStreamingController::class, 'searchStreamingPlatformsForTitle']);

        // Simular respuestas de las APIs externas
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [['id' => 123, 'title' => 'Test Movie', 'release_date' => '2020-01-01']]
            ]),
            'api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => 'tt1234567'
            ]),
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 456]]
            ]),
            'api.watchmode.com/v1/title/456/sources*' => Http::response([
                [
                    'name' => 'Netflix',
                    'type' => 'sub',
                    'format' => 'HD',
                    'price' => null,
                    'web_url' => 'https://netflix.com/watch'
                ]
            ])
        ]);

        $response = $this->postJson('/testing/search-streaming', [
            'title' => 'Test Movie',
            'region' => 'ES',
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'platform' => 'Netflix',
            'type' => 'sub',
            'format' => 'HD',
            'price' => 'Incluido',
            'url' => 'https://netflix.com/watch',
        ]);
    }
}
