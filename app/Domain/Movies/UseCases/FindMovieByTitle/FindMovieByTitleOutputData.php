<?php

namespace App\Domain\Movies\UseCases\FindMovieByTitle;

/** Simple boundary data; never carries framework objects or Entities. */
final readonly class FindMovieByTitleOutputData
{
    public function __construct(public ?\App\Domain\Movies\UseCases\Data\MovieData $movie) {}
}
