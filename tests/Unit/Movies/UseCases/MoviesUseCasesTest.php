<?php

namespace Tests\Unit\Movies\UseCases;

use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInputData;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInteractor;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsOutputBoundary;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsOutputData;
use App\Domain\Movies\UseCases\Data\MovieData;
use App\Domain\Movies\UseCases\Data\StreamingOfferData;
use App\Domain\Movies\UseCases\DataAccessInterfaces\MovieLookup;
use App\Domain\Movies\UseCases\DataAccessInterfaces\StreamingOfferLookup;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleInputData;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleInteractor;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleInputData;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleInteractor;
use App\Domain\Movies\UseCases\ProviderUnavailable;

use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleOutputBoundary;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleOutputBoundary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoviesUseCasesTest extends TestCase
{
    private function lookup(array $byMovie): StreamingOfferLookup
    {
        return new class($byMovie) implements StreamingOfferLookup
        {
            public function __construct(private array $byMovie) {}

            public function forMovie(string $movieId, string $region): array
            {
                $result = $this->byMovie[$movieId] ?? [];
                if ($result instanceof \Throwable) {
                    throw $result;
                }

                return $result;
            }
        };
    }

    private function offer(string $platform, string $type = 'sub', string $region = 'ES', ?string $price = null, ?string $currency = null): StreamingOfferData
    {
        return new StreamingOfferData($platform, $platform, $region, $type, 'HD', $price, $currency, 'https://example.com/watch');
    }

    public function test_analysis_filters_deduplicates_and_ranks_without_laravel(): void
    {
        $output = new class implements AnalyzeSubscriptionPlatformsOutputBoundary
        {
            public ?AnalyzeSubscriptionPlatformsOutputData $data = null;

            public function present(AnalyzeSubscriptionPlatformsOutputData $output): void
            {
                $this->data = $output;
            }
        };
        $useCase = new AnalyzeSubscriptionPlatformsInteractor($this->lookup([
            'a' => [$this->offer('Netflix'), $this->offer('Netflix'), $this->offer('Alpha'), $this->offer('BuyOnly', 'buy'), $this->offer('WrongRegion', 'sub', 'US')],
            'b' => [$this->offer('Netflix'), $this->offer('Beta')],
        ]), $output);
        $useCase->execute(new AnalyzeSubscriptionPlatformsInputData([
            new MovieData('a', 'First', '2020'), new MovieData('b', 'Second', '2021'),
        ], 'ES'));
        self::assertSame(['Netflix', 'Alpha', 'Beta'], array_column($output->data->platforms, 'name'));
        self::assertSame([2, 1, 1], array_column($output->data->platforms, 'count'));
        self::assertInstanceOf(MovieData::class, $output->data->platforms[0]['movies'][0]);
        self::assertSame('First', $output->data->platforms[0]['movies'][0]->title);
    }

    public static function invalidSelections(): array
    {
        return [
            'empty' => [[]],
            'too many' => [array_map(fn ($id) => new MovieData((string) $id, 'Movie', '2020'), range(1, 31))],
            'duplicates' => [[new MovieData('1', 'A', ''), new MovieData('1', 'A', '')]],
        ];
    }

    #[DataProvider('invalidSelections')]
    public function test_invalid_selection_is_rejected_before_lookups(array $movies): void
    {
        $lookup = $this->createMock(StreamingOfferLookup::class);
        $lookup->expects(self::never())->method('forMovie');
        $presenter = $this->createMock(AnalyzeSubscriptionPlatformsOutputBoundary::class);
        $presenter->expects(self::never())->method('present');
        $this->expectException(\InvalidArgumentException::class);
        (new AnalyzeSubscriptionPlatformsInteractor($lookup, $presenter))
            ->execute(new AnalyzeSubscriptionPlatformsInputData($movies, 'ES'));
    }

    public function test_thirty_movies_are_allowed(): void
    {
        $presenter = $this->createMock(AnalyzeSubscriptionPlatformsOutputBoundary::class);
        $presenter->expects(self::once())->method('present')->with(self::callback(fn ($output) => $output->platforms === []));
        (new AnalyzeSubscriptionPlatformsInteractor($this->lookup([]), $presenter))->execute(
            new AnalyzeSubscriptionPlatformsInputData(array_map(fn ($id) => new MovieData((string) $id, 'Movie', ''), range(1, 30)), 'ES'));
    }

    public function test_failure_on_second_movie_never_publishes_partial_results(): void
    {
        $presenter = $this->createMock(AnalyzeSubscriptionPlatformsOutputBoundary::class);
        $presenter->expects(self::never())->method('present');
        $useCase = new AnalyzeSubscriptionPlatformsInteractor($this->lookup([
            '1' => [$this->offer('Netflix')],
            '2' => new ProviderUnavailable('Unavailable'),
        ]), $presenter);
        $this->expectException(ProviderUnavailable::class);
        $useCase->execute(new AnalyzeSubscriptionPlatformsInputData([
            new MovieData('1', 'A', ''), new MovieData('2', 'B', ''),
        ], 'ES'));
    }

    public function test_movie_title_is_normalized_and_lookup_data_is_published(): void
    {
        $lookup = $this->createMock(MovieLookup::class);
        $lookup->expects(self::once())->method('findFirstByTitle')->with('Movie')->willReturn(new MovieData('1', 'Movie', '2020'));
        $presenter = $this->createMock(FindMovieByTitleOutputBoundary::class);
        $presenter->expects(self::once())->method('present')->with(self::callback(
            fn ($output) => $output->movie->id === '1' && $output->movie->title === 'Movie' && $output->movie->year === '2020'
        ));
        (new FindMovieByTitleInteractor($lookup, $presenter))->execute(new FindMovieByTitleInputData(' Movie '));
    }

    public function test_no_movie_is_a_successful_empty_result(): void
    {
        $lookup = $this->createMock(MovieLookup::class);
        $lookup->method('findFirstByTitle')->willReturn(null);
        $offers = $this->createMock(StreamingOfferLookup::class);
        $offers->expects(self::never())->method('forMovie');
        $presenter = $this->createMock(FindStreamingOffersForTitleOutputBoundary::class);
        $presenter->expects(self::once())->method('present')->with(self::callback(fn ($output) => $output->offers === []));
        (new FindStreamingOffersForTitleInteractor($lookup, $offers, $presenter))
            ->execute(new FindStreamingOffersForTitleInputData('Unknown', 'ES'));
    }

    public function test_offer_deduplication_preserves_price_data(): void
    {
        $lookup = $this->createMock(MovieLookup::class);
        $lookup->method('findFirstByTitle')->willReturn(new MovieData('1', 'Movie', ''));
        $offer = $this->offer('Shop', 'rent', 'US', '3.99', 'USD');
        $presenter = $this->createMock(FindStreamingOffersForTitleOutputBoundary::class);
        $presenter->expects(self::once())->method('present')->with(self::callback(
            fn ($output) => count($output->offers) === 3
                && $output->offers[0] === $offer
                && $output->offers[1]->price === null
                && $output->offers[2]->currency === null
        ));
        (new FindStreamingOffersForTitleInteractor($lookup, $this->lookup([
            '1' => [$offer, $offer, $this->offer('UnknownPrice', 'buy', 'US'), $this->offer('NoCurrency', 'rent', 'US', '5')],
        ]), $presenter))->execute(new FindStreamingOffersForTitleInputData('Movie', 'US'));
    }

    public function test_invalid_region_is_rejected_without_external_calls(): void
    {
        $lookup = $this->createMock(MovieLookup::class);
        $lookup->expects(self::never())->method('findFirstByTitle');
        $this->expectException(\InvalidArgumentException::class);
        (new FindStreamingOffersForTitleInteractor($lookup, $this->lookup([]), $this->createMock(FindStreamingOffersForTitleOutputBoundary::class)))
            ->execute(new FindStreamingOffersForTitleInputData('Movie', 'invalid'));
    }
}
