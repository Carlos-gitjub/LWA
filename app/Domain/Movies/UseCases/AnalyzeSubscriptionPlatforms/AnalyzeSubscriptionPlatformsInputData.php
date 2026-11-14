<?php

namespace App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms;

/** Simple boundary data; never carries framework objects or Entities. */
final readonly class AnalyzeSubscriptionPlatformsInputData
{
    /** @param list<\App\Domain\Movies\UseCases\Data\MovieData> $movies */
    public function __construct(public array $movies, public string $region) {}
}
