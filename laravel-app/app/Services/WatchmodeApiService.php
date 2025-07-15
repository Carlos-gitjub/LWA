<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WatchmodeApiService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.watchmode.key');
    }

    public function searchByImdb(string $imdbId): ?array
    {
        $res = Http::get("https://api.watchmode.com/v1/search/", [
            'apiKey' => $this->apiKey,
            'search_field' => 'imdb_id',
            'search_value' => $imdbId
        ]);

        return $res->json('title_results.0');
    }

    public function getSources(int $watchmodeId, string $region): array
    {
        $res = Http::get("https://api.watchmode.com/v1/title/{$watchmodeId}/sources/", [
            'apiKey' => $this->apiKey,
            'regions' => $region
        ]);

        return $res->json();
    }

    public function getStreamingPlatforms(int $tmdbId, string $region): array
    {
        // Obtener el IMDb ID a partir del ID de TMDb
        $tmdbService = app(TmdbApiService::class);
        $imdbId = $tmdbService->getImdbId($tmdbId);

        if (!$imdbId) {
            return [];
        }

        // Buscar el ID de Watchmode
        $watchmodeTitle = $this->searchByImdb($imdbId);

        if (!$watchmodeTitle || !isset($watchmodeTitle['id'])) {
            return [];
        }

        // Obtener las fuentes (plataformas)
        $sources = $this->getSources($watchmodeTitle['id'], $region);

        // Filtrar por tipo de acceso: solo suscripción
        $subscriptionPlatforms = collect($sources)
            ->filter(fn($s) => $s['type'] === 'sub')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        return $subscriptionPlatforms;
    }

}
