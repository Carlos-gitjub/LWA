<?php

namespace App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms;

/** Simple boundary data; never carries framework objects or Entities. */
final readonly class AnalyzeSubscriptionPlatformsOutputData
{
    /** @param list<array{id: string, name: string, count: int, movies: list<\App\Domain\Movies\UseCases\Data\MovieData>}> $platforms */
    public function __construct(public array $platforms) {}
}
