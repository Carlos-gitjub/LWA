<?php

namespace App\Infrastructure\Movies\HttpClients;

use Illuminate\Support\Facades\Http;

class TmdbApiClient
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key');
    }

    public function searchMovieFirstMatchByTitle(string $query): ?array
    {
        $res = Http::timeout(15)->connectTimeout(5)->get('https://api.themoviedb.org/3/search/movie', [
            'api_key' => $this->apiKey,
            'query' => $query,
        ]);

        $res->throw();
        $results = $res->json('results');
        if (! is_array($results) || ! array_is_list($results) || (isset($results[0]) && ! is_array($results[0]))) {
            throw new \App\Domain\Movies\UseCases\ProviderUnavailable('Invalid movie response.');
        }

        return $results[0] ?? null;
    }

    public function fetchImdbIdFromTmdbId(int $tmdbId): ?string
    {
        $res = Http::timeout(15)->connectTimeout(5)->get("https://api.themoviedb.org/3/movie/{$tmdbId}/external_ids", [
            'api_key' => $this->apiKey,
        ]);

        $res->throw();
        $data = $res->json();
        if (! is_array($data) || ! array_key_exists('imdb_id', $data) ||
            ($data['imdb_id'] !== null && ! is_string($data['imdb_id']))) {
            throw new \App\Domain\Movies\UseCases\ProviderUnavailable('Invalid movie identifier response.');
        }

        return $data['imdb_id'];
    }
}
