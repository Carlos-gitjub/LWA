<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbApiService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key');
    }

    public function searchMovieFirstMatchByTitle(string $query): ?array
    {
        $res = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $this->apiKey,
            'query' => $query
        ]);

        return $res->json('results.0');
    }

    public function fetchImdbIdFromTmdbId(int $tmdbId): ?string
    {
        $res = Http::get("https://api.themoviedb.org/3/movie/{$tmdbId}/external_ids", [
            'api_key' => $this->apiKey
        ]);

        return $res->json('imdb_id');
    }
}
