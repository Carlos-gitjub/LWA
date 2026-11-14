<?php

namespace Tests\Unit\Movies\Presenters;

use App\Domain\Movies\UseCases\Data\StreamingOfferData;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleOutputData;
use App\Http\Presenters\Movies\FindStreamingOffersForTitlePresenter;
use PHPUnit\Framework\TestCase;

final class FindStreamingOffersForTitlePresenterTest extends TestCase
{
    public function test_prices_and_access_types_are_formatted_for_display(): void
    {
        $offer = fn ($type, $price, $currency) => new StreamingOfferData('1', 'Shop', 'US', $type, 'HD', $price, $currency, '');
        $presenter = new FindStreamingOffersForTitlePresenter;
        $presenter->present(new FindStreamingOffersForTitleOutputData([
            $offer('rent', '3.99', 'USD'),
            $offer('buy', null, null),
            $offer('rent', '5', null),
        ]));
        self::assertSame(['3.99 USD', 'Price unavailable', '5 (currency unavailable)'], array_column($presenter->viewModel->data, 'price'));
        self::assertSame('Rental', $presenter->viewModel->data[0]['typeLabel']);
    }
}
