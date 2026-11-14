<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\DeleteBook\DeleteBookOutputBoundary;
use App\Domain\Library\UseCases\DeleteBook\DeleteBookOutputData;

final class DeleteBookPresenter implements DeleteBookOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(DeleteBookOutputData $output): void
    {
        $data = ['message' => 'Libro eliminado correctamente'];
        $this->viewModel = new ViewModel($data);
    }
}
