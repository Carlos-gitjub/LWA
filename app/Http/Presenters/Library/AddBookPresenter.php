<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\AddBook\AddBookOutputBoundary;
use App\Domain\Library\UseCases\AddBook\AddBookOutputData;

final class AddBookPresenter implements AddBookOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(AddBookOutputData $output): void
    {
        $data = ['book' => BookViewData::from($output->book)];
        $this->viewModel = new ViewModel($data);
    }
}
