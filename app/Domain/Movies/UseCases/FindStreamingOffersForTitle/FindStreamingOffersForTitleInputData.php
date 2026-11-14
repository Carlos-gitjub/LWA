<?php

namespace App\Domain\Movies\UseCases\FindStreamingOffersForTitle;

/** Simple boundary data; never carries framework objects or Entities. */
final readonly class FindStreamingOffersForTitleInputData
{
    public function __construct(public string $title, public string $region) {}
}
