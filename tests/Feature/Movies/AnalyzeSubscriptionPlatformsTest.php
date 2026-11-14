<?php

namespace Tests\Feature\Movies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AnalyzeSubscriptionPlatformsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    public function test_failed_second_movie_returns_no_partial_analysis(): void
    {
        Http::fake([
            'api.themoviedb.org/3/movie/1/external_ids*' => Http::response(['imdb_id' => 'tt1']),
            'api.themoviedb.org/3/movie/2/external_ids*' => Http::response([], 503),
            'api.watchmode.com/v1/search*' => Http::response(['title_results' => [['id' => 10]]]),
            'api.watchmode.com/v1/title/10/sources*' => Http::response([['name' => 'Netflix', 'type' => 'sub']]),
        ]);
        $this->postJson('/movies-streaming/advanced/subscription-most/analyze', ['movies' => [
            ['id' => 1, 'title' => 'A', 'year' => '2020'],
            ['id' => 2, 'title' => 'B', 'year' => '2021'],
        ], 'region' => 'ES'])->assertStatus(503)->assertExactJson([
            'message' => 'Could not complete the operation. Please try again.',
        ]);
    }

    public function test_invalid_selections_never_call_provider(): void
    {
        Http::fake();
        $movie = ['id' => 1, 'title' => 'Movie', 'year' => '2020'];
        foreach ([[], [$movie, $movie], array_fill(0, 31, $movie)] as $selection) {
            $this->postJson('/movies-streaming/advanced/subscription-most/analyze', ['movies' => $selection, 'region' => 'ES'])->assertStatus(422);
        }
        Http::assertNothingSent();
    }

    public function test_empty_availability_is_successful(): void
    {
        Http::fake([
            'api.themoviedb.org/3/movie/1/external_ids*' => Http::response(['imdb_id' => 'tt1']),
            'api.watchmode.com/v1/search*' => Http::response(['title_results' => [['id' => 10]]]),
            'api.watchmode.com/v1/title/10/sources*' => Http::response([]),
        ]);
        $this->postJson('/movies-streaming/advanced/subscription-most/analyze', ['movies' => [
            ['id' => 1, 'title' => 'Movie', 'year' => '2020'],
        ], 'region' => 'ES'])->assertOk()->assertExactJson([]);
    }
}
