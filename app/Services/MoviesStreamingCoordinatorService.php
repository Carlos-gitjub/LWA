<?php
namespace App\Services;

use App\Services\WatchmodeApiService;
use App\Services\TmdbApiService;

class MoviesStreamingCoordinatorService
{
    public function __construct(
        protected TmdbApiService $tmdb,
        protected WatchmodeApiService $watchmode
    ) {}

    public function getStreamingPlatformsForTitle(string $title, string $region): array
    {
        $tmdbResult = $this->tmdb->searchMovieFirstMatchByTitle($title);
        if (!$tmdbResult) return [];

        $imdbId = $this->tmdb->fetchImdbIdFromTmdbId($tmdbResult['id']);
        if (!$imdbId) return [];

        $watchmodeMovie = $this->watchmode->searchTitleFirstMatchByImdbId($imdbId);
        if (!$watchmodeMovie) return [];

        $sources = $this->watchmode->getStreamingSourcesByWatchmodeId($watchmodeMovie['id'], $region);

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

    public function getSubscriptionPlatformsForList(array $movies, string $region): array
    {
        $platformMovieMap = [];

        foreach ($movies as $movie) {
            $tmdbId = $movie['id'];
            $title = $movie['title'];
            $year = $movie['year'];

            $platforms = $this->watchmode->getStreamingPlatformsByTmdbId($tmdbId, $region);

            foreach ($platforms as $platform) {
                $platformMovieMap[$platform][] = "{$title} ({$year})";
            }
        }

        return collect($platformMovieMap)
            ->map(fn($movies) => ['count' => count($movies), 'movies' => $movies])
            ->sortByDesc('count')
            ->all();
    }

}
