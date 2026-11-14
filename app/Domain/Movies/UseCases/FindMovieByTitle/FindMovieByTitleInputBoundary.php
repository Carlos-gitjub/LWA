<?php

namespace App\Domain\Movies\UseCases\FindMovieByTitle;

interface FindMovieByTitleInputBoundary
{
    public function execute(FindMovieByTitleInputData $input): void;
}
