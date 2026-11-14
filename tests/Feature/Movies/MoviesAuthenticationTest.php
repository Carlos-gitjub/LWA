<?php

namespace Tests\Feature\Movies;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class MoviesAuthenticationTest extends TestCase
{
    public static function endpoints(): array
    {
        return [
            ['/api/movies/search-title'],
            ['/movies-streaming/search'],
            ['/movies-streaming/advanced/subscription-most/analyze'],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_guests_cannot_invoke_movie_operations(string $endpoint): void
    {
        Http::fake();
        $this->postJson($endpoint, [])->assertUnauthorized();
        Http::assertNothingSent();
    }
}
