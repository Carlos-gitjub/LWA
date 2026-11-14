<?php

namespace App\Http\Presenters\Movies;

use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleOutputBoundary;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleOutputData;

final class FindMovieByTitlePresenter implements FindMovieByTitleOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(FindMovieByTitleOutputData $output): void
    {
        $movie = $output->movie;
        $this->viewModel = new ViewModel($movie ? [
            'id' => $movie->id, 'title' => $movie->title, 'year' => $movie->year,
            'label' => $movie->year === '' ? $movie->title : "{$movie->title} ({$movie->year})",
        ] : null);
    }
}
