<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WatchmodeApiService
{
    protected string $apiKey;

    public function __construct(
        protected TmdbApiService $tmdb
    ) {
        $this->apiKey = config('services.watchmode.key');
    }

    public function searchTitleFirstMatchByImdbId(string $imdbId): ?array
    {
        $res = Http::get("https://api.watchmode.com/v1/search/", [
            'apiKey' => $this->apiKey,
            'search_field' => 'imdb_id',
            'search_value' => $imdbId
        ]);

        return $res->json('title_results.0');
    }

    public function getStreamingSourcesByWatchmodeId(int $watchmodeId, string $region): array
    {
        $res = Http::get("https://api.watchmode.com/v1/title/{$watchmodeId}/sources/", [
            'apiKey' => $this->apiKey,
            'regions' => $region
        ]);

        return $res->json();
    }

    public function getStreamingPlatformsByTmdbId(int $tmdbId, string $region): array
    {
        $imdbId = $this->tmdb->fetchImdbIdFromTmdbId($tmdbId);
        if (!$imdbId) {
            return [];
        }

        $watchmodeTitle = $this->searchTitleFirstMatchByImdbId($imdbId);
        if (!$watchmodeTitle || !isset($watchmodeTitle['id'])) {
            return [];
        }

        $sources = $this->getStreamingSourcesByWatchmodeId($watchmodeTitle['id'], $region);

        $subscriptionPlatforms = collect($sources)
            ->filter(fn($s) => $s['type'] === 'sub')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        return $subscriptionPlatforms;
    }

}
