<?php

namespace App\Domain\Movies\UseCases\FindMovieByTitle;

use App\Domain\Movies\UseCases\DataAccessInterfaces\MovieLookup;
use App\Domain\Movies\UseCases\InputRules;

final class FindMovieByTitleInteractor implements FindMovieByTitleInputBoundary
{
    public function __construct(private MovieLookup $movies, private FindMovieByTitleOutputBoundary $output) {}

    public function execute(FindMovieByTitleInputData $input): void
    {
        $movie = $this->movies->findFirstByTitle(InputRules::title($input->title));
        $this->output->present(new FindMovieByTitleOutputData($movie));
    }
}
