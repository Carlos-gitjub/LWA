<?php

namespace App\Infrastructure\Movies\Adapters;

use App\Domain\Movies\UseCases\Data\MovieData;
use App\Domain\Movies\UseCases\DataAccessInterfaces\MovieLookup;
use App\Domain\Movies\UseCases\ProviderUnavailable;
use App\Infrastructure\Movies\HttpClients\TmdbApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

final class TmdbMovieLookup implements MovieLookup
{
    public function __construct(private TmdbApiClient $tmdb) {}

    public function findFirstByTitle(string $title): ?MovieData
    {
        try {
            $row = $this->tmdb->searchMovieFirstMatchByTitle($title);
            if ($row === null) {
                return null;
            }
            if (! isset($row['id'], $row['title']) || ! is_numeric($row['id']) || ! is_string($row['title'])) {
                throw new ProviderUnavailable('Invalid movie response.');
            }

            return new MovieData((string) $row['id'], $row['title'], substr((string) ($row['release_date'] ?? ''), 0, 4));
        } catch (ConnectionException|RequestException $e) {
            throw new ProviderUnavailable('Movie lookup unavailable.', 0, $e);
        }
    }
}
