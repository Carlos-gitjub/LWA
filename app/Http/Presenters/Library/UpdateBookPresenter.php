<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\UpdateBook\UpdateBookOutputBoundary;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookOutputData;

final class UpdateBookPresenter implements UpdateBookOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(UpdateBookOutputData $output): void
    {
        $data = ['book' => BookViewData::from($output->book)];
        $this->viewModel = new ViewModel($data);
    }
}
