<?php

namespace Tests\Feature\Movies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FindMovieByTitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    public function test_no_match_returns_json_null(): void
    {
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::response(['results' => []])]);
        $response = $this->postJson('/api/movies/search-title', ['title' => 'Unknown']);
        $response->assertOk();
        self::assertSame('null', $response->getContent());
    }

    public function test_search_returns_movie_view_model(): void
    {
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::response([
            'results' => [['id' => 1, 'title' => 'Movie', 'release_date' => '2020-01-01']],
        ])]);
        $this->postJson('/api/movies/search-title', ['title' => 'Movie'])->assertOk()->assertJsonPath('label', 'Movie (2020)');
    }

    public function test_provider_http_failure_returns_503_not_empty_success(): void
    {
        Http::fake(['*' => Http::response(['error' => 'private provider detail'], 500)]);
        $this->postJson('/api/movies/search-title', ['title' => 'Movie'])
            ->assertStatus(503)->assertExactJson(['message' => 'Could not complete the operation. Please try again.']);
    }

    public function test_connection_failure_returns_503(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Timeout'));
        $this->postJson('/api/movies/search-title', ['title' => 'Movie'])->assertStatus(503);
    }

    public function test_malformed_provider_response_returns_503(): void
    {
        Http::fake(['*' => Http::response(['unexpected' => 'shape'])]);
        $this->postJson('/api/movies/search-title', ['title' => 'Movie'])->assertStatus(503);
    }
}
