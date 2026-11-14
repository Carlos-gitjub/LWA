<?php

namespace Tests\Unit\Movies\Presenters;

use App\Domain\Movies\UseCases\Data\MovieData;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleOutputData;
use App\Http\Presenters\Movies\FindMovieByTitlePresenter;
use PHPUnit\Framework\TestCase;

final class FindMovieByTitlePresenterTest extends TestCase
{
    public function test_movie_label_includes_release_year(): void
    {
        $presenter = new FindMovieByTitlePresenter;
        $presenter->present(new FindMovieByTitleOutputData(new MovieData('1', 'Movie', '2020')));
        self::assertSame('Movie (2020)', $presenter->viewModel->data['label']);
    }
}
