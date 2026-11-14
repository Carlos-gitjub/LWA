<?php

namespace App\Domain\Movies\UseCases\FindStreamingOffersForTitle;

/** Simple boundary data; never carries framework objects or Entities. */
final readonly class FindStreamingOffersForTitleOutputData
{
    /** @param list<\App\Domain\Movies\UseCases\Data\StreamingOfferData> $offers */
    public function __construct(public array $offers) {}
}
