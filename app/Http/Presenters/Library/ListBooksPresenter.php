<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\ListBooks\ListBooksOutputBoundary;
use App\Domain\Library\UseCases\ListBooks\ListBooksOutputData;

final class ListBooksPresenter implements ListBooksOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(ListBooksOutputData $output): void
    {
        $data = array_map(BookViewData::from(...), $output->books);
        $this->viewModel = new ViewModel($data);
    }
}
