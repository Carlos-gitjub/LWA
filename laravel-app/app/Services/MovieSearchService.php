<?php
namespace App\Services;

use App\Services\WatchmodeApiService;
use App\Services\TmdbApiService;

class MovieSearchService
{
    public function __construct(
        protected TmdbApiService $tmdb,
        protected WatchmodeApiService $watchmode
    ) {}

    public function search(string $title, string $region): array
    {
        $tmdbResult = $this->tmdb->searchMovie($title);
        if (!$tmdbResult) return [];

        $imdbId = $this->tmdb->getImdbId($tmdbResult['id']);
        if (!$imdbId) return [];

        $watchmodeMovie = $this->watchmode->searchByImdb($imdbId);
        if (!$watchmodeMovie) return [];

        $sources = $this->watchmode->getSources($watchmodeMovie['id'], $region);

        return collect($sources)->unique(function ($s) {
            return $s['name'] . $s['type'] . $s['format'] . $s['price'] . $s['web_url'];
        })->map(function ($s) {
            return [
                'platform' => $s['name'],
                'type' => $s['type'],
                'format' => $s['format'],
                'price' => is_null($s['price']) ? 'Incluido' : $s['price'] . ' €',
                'url' => $s['web_url']
            ];
        })->values()->all();
    }
}
