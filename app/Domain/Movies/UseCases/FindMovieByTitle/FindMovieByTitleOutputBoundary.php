<?php

namespace App\Domain\Movies\UseCases\FindMovieByTitle;

interface FindMovieByTitleOutputBoundary
{
    public function present(FindMovieByTitleOutputData $output): void;
}
