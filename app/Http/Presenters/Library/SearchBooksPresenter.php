<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\SearchBooks\SearchBooksOutputBoundary;
use App\Domain\Library\UseCases\SearchBooks\SearchBooksOutputData;

final class SearchBooksPresenter implements SearchBooksOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(SearchBooksOutputData $output): void
    {
        $data = array_map(BookViewData::from(...), $output->books);
        $this->viewModel = new ViewModel($data);
    }
}
