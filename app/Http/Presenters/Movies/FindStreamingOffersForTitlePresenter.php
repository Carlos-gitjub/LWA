<?php

namespace App\Http\Presenters\Movies;

use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleOutputBoundary;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleOutputData;

final class FindStreamingOffersForTitlePresenter implements FindStreamingOffersForTitleOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(FindStreamingOffersForTitleOutputData $output): void
    {
        $this->viewModel = new ViewModel(array_map(fn ($offer) => [
            'platform' => $offer->platform, 'type' => $offer->type,
            'typeLabel' => match ($offer->type) {
                'sub' => 'Subscription', 'rent' => 'Rental', 'buy' => 'Purchase', default => $offer->type,
            },
            'format' => $offer->format,
            'price' => $this->price($offer),
            'url' => $offer->url,
        ], $output->offers));
    }

    private function price(\App\Domain\Movies\UseCases\Data\StreamingOfferData $offer): string
    {
        if ($offer->price === null) {
            return $offer->type === 'sub' ? 'Incluido' : 'Price unavailable';
        }

        return $offer->price.($offer->currency ? ' '.$offer->currency : ' (currency unavailable)');
    }
}
