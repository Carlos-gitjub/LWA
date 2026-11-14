<?php

namespace App\Domain\Movies\UseCases\FindStreamingOffersForTitle;

use App\Domain\Movies\UseCases\DataAccessInterfaces\MovieLookup;
use App\Domain\Movies\UseCases\DataAccessInterfaces\StreamingOfferLookup;
use App\Domain\Movies\UseCases\InputRules;

final class FindStreamingOffersForTitleInteractor implements FindStreamingOffersForTitleInputBoundary
{
    public function __construct(
        private MovieLookup $movies,
        private StreamingOfferLookup $offers,
        private FindStreamingOffersForTitleOutputBoundary $output,
    ) {}

    public function execute(FindStreamingOffersForTitleInputData $input): void
    {
        $title = InputRules::title($input->title);
        $region = InputRules::region($input->region);
        $movie = $this->movies->findFirstByTitle($title);
        $unique = [];
        foreach ($movie ? $this->offers->forMovie($movie->id, $region) : [] as $offer) {
            if ($offer->region !== $region) {
                continue;
            }
            $key = serialize([$offer->platformId, $offer->type, $offer->format, $offer->price, $offer->currency, $offer->url]);
            $unique[$key] = $offer;
        }
        $this->output->present(new FindStreamingOffersForTitleOutputData(array_values($unique)));
    }
}
