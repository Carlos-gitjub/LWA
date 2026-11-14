<?php

namespace App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms;

use App\Domain\Movies\Entities\Movie;
use App\Domain\Movies\Entities\StreamingOffer;
use App\Domain\Movies\Entities\StreamingPlatform;
use App\Domain\Movies\UseCases\Data\MovieData;
use App\Domain\Movies\UseCases\DataAccessInterfaces\StreamingOfferLookup;
use App\Domain\Movies\UseCases\InputRules;

final class AnalyzeSubscriptionPlatformsInteractor implements AnalyzeSubscriptionPlatformsInputBoundary
{
    public const MAX_MOVIES = 30;

    public function __construct(private StreamingOfferLookup $offers, private AnalyzeSubscriptionPlatformsOutputBoundary $output) {}

    public function execute(AnalyzeSubscriptionPlatformsInputData $input): void
    {
        $region = InputRules::region($input->region);
        if (count($input->movies) < 1 || count($input->movies) > self::MAX_MOVIES) {
            throw new \InvalidArgumentException('Select between 1 and 30 movies.');
        }
        $movies = [];
        foreach ($input->movies as $data) {
            if (! $data instanceof MovieData || trim($data->id) === '' || trim($data->title) === '') {
                throw new \InvalidArgumentException('Each selection must identify a movie.');
            }
            if (isset($movies[$data->id])) {
                throw new \InvalidArgumentException('Select each movie only once.');
            }
            $movies[$data->id] = new Movie($data->id, $data->title, $data->year);
        }
        $platforms = [];
        foreach ($movies as $movie) {
            foreach ($this->offers->forMovie($movie->id, $region) as $data) {
                $offer = new StreamingOffer($movie->id,
                    new StreamingPlatform($data->platformId, $data->platform),
                    $data->region, $data->type, $data->format, $data->price, $data->currency, $data->url);
                if (! $offer->isSubscription() || $offer->region !== $region) {
                    continue;
                }
                $id = $offer->platform->id;
                $platforms[$id] ??= ['id' => $id, 'name' => $offer->platform->name, 'movies' => []];
                $platforms[$id]['movies'][$movie->id] = new MovieData($movie->id, $movie->title, $movie->year);
            }
        }
        foreach ($platforms as &$platform) {
            $platform['movies'] = array_values($platform['movies']);
            $platform['count'] = count($platform['movies']);
        }
        unset($platform);
        $platforms = array_values($platforms);
        usort($platforms, fn ($a, $b) => ($b['count'] <=> $a['count']) ?: strcmp($a['name'], $b['name']));
        // Publish once, only after every lookup succeeded: no partial results.
        $this->output->present(new AnalyzeSubscriptionPlatformsOutputData($platforms));
    }
}
