<?php

namespace App\Domain\Library\UseCases\DeleteBook;

use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;

final class DeleteBookInteractor implements DeleteBookInputBoundary
{
    public function __construct(private BookRepository $books, private BookFileStorage $files, private DeleteBookOutputBoundary $output) {}

    public function execute(DeleteBookInputData $input): void
    {
        $book = $this->books->find($input->id);
        $this->books->delete($input->id);
        $this->files->delete($book->filePath);
        $this->files->delete($book->coverPath);
        $this->output->present(new DeleteBookOutputData($input->id));
    }
}
