<?php

namespace App\Infrastructure\Movies\HttpClients;

use Illuminate\Support\Facades\Http;

class WatchmodeApiClient
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.watchmode.key');
    }

    public function searchTitleFirstMatchByImdbId(string $imdbId): ?array
    {
        $res = Http::timeout(15)->connectTimeout(5)->get('https://api.watchmode.com/v1/search/', [
            'apiKey' => $this->apiKey,
            'search_field' => 'imdb_id',
            'search_value' => $imdbId,
        ]);

        $res->throw();
        $results = $res->json('title_results');
        if (! is_array($results) || ! array_is_list($results) || (isset($results[0]) && ! is_array($results[0]))) {
            throw new \App\Domain\Movies\UseCases\ProviderUnavailable('Invalid title response.');
        }

        return $results[0] ?? null;
    }

    public function getStreamingSourcesByWatchmodeId(int $watchmodeId, string $region): array
    {
        $res = Http::timeout(15)->connectTimeout(5)->get("https://api.watchmode.com/v1/title/{$watchmodeId}/sources/", [
            'apiKey' => $this->apiKey,
            'regions' => $region,
        ]);

        $res->throw();
        $sources = $res->json();
        if (! is_array($sources) || ! array_is_list($sources)) {
            throw new \App\Domain\Movies\UseCases\ProviderUnavailable('Invalid streaming response.');
        }

        return $sources;
    }
}
