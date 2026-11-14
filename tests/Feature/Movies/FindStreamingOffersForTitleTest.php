<?php

namespace Tests\Feature\Movies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FindStreamingOffersForTitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function it_returns_streaming_platforms_for_title()
    {
        // Simular respuestas de las APIs externas
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [['id' => 123, 'title' => 'Test Movie', 'release_date' => '2020-01-01']],
            ]),
            'api.themoviedb.org/3/movie/123/external_ids*' => Http::response([
                'imdb_id' => 'tt1234567',
            ]),
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 456]],
            ]),
            'api.watchmode.com/v1/title/456/sources*' => Http::response([
                [
                    'name' => 'Netflix',
                    'type' => 'sub',
                    'format' => 'HD',
                    'price' => null,
                    'web_url' => 'https://netflix.com/watch',
                ],
            ]),
        ]);

        $response = $this->postJson('/movies-streaming/search', [
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
