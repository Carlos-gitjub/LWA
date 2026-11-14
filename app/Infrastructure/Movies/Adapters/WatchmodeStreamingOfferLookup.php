<?php

namespace App\Infrastructure\Movies\Adapters;

use App\Domain\Movies\UseCases\Data\StreamingOfferData;
use App\Domain\Movies\UseCases\DataAccessInterfaces\StreamingOfferLookup;
use App\Domain\Movies\UseCases\ProviderUnavailable;
use App\Infrastructure\Movies\HttpClients\TmdbApiClient;
use App\Infrastructure\Movies\HttpClients\WatchmodeApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

final class WatchmodeStreamingOfferLookup implements StreamingOfferLookup
{
    public function __construct(private TmdbApiClient $tmdb, private WatchmodeApiClient $watchmode) {}

    public function forMovie(string $movieId, string $region): array
    {
        // The reference is opaque to the application. This adapter resolves its provider meaning.
        if (! ctype_digit($movieId) || (int) $movieId < 1) {
            throw new \InvalidArgumentException('Invalid movie reference.');
        }
        try {
            $imdb = $this->tmdb->fetchImdbIdFromTmdbId((int) $movieId);
            if (! $imdb) {
                return [];
            }
            $title = $this->watchmode->searchTitleFirstMatchByImdbId($imdb);
            if ($title === null) {
                return [];
            }
            if (! isset($title['id']) || ! is_numeric($title['id'])) {
                throw new ProviderUnavailable('Invalid title response.');
            }
            $result = [];
            foreach ($this->watchmode->getStreamingSourcesByWatchmodeId((int) $title['id'], $region) as $row) {
                if (! is_array($row) || ! isset($row['name'], $row['type']) ||
                    ! is_string($row['name']) || ! is_string($row['type']) ||
                    (isset($row['price']) && ! is_numeric($row['price']))) {
                    throw new ProviderUnavailable('Invalid offer response.');
                }
                foreach (['format', 'web_url', 'region', 'currency'] as $field) {
                    if (isset($row[$field]) && ! is_string($row[$field])) {
                        throw new ProviderUnavailable('Invalid offer response.');
                    }
                }
                $result[] = new StreamingOfferData(
                    (string) ($row['source_id'] ?? $row['name']), $row['name'],
                    strtoupper($row['region'] ?? $region), $row['type'], $row['format'] ?? '',
                    isset($row['price']) ? (string) $row['price'] : null,
                    $row['currency'] ?? null, $row['web_url'] ?? '',
                );
            }

            return $result;
        } catch (ConnectionException|RequestException $e) {
            throw new ProviderUnavailable('Streaming lookup unavailable.', 0, $e);
        }
    }
}
