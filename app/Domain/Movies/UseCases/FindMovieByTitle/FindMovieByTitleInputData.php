<?php

namespace App\Domain\Movies\UseCases\FindMovieByTitle;

/** Simple boundary data; never carries framework objects or Entities. */
final readonly class FindMovieByTitleInputData
{
    public function __construct(public string $title) {}
}
