<?php

namespace Tests\Unit\Library;

use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;
use App\Domain\Library\UseCases\DataAccessInterfaces\PdfTextExtractor;
use App\Domain\Library\UseCases\InvalidPdf;
use App\Domain\Library\UseCases\LibraryUnavailable;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookInputData;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookInteractor;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookOutputBoundary;
use PHPUnit\Framework\TestCase;

final class UpdateBookInteractorTest extends TestCase
{
    public function test_extraction_failure_does_not_change_persistence_or_files(): void
    {
        $books = $this->createMock(BookRepository::class);
        $books->method('find')->willReturn(new BookData(1, 'Old', null, 'old.pdf', 'old.jpg'));
        $books->expects(self::never())->method('save');
        $files = $this->createMock(BookFileStorage::class);
        $files->expects(self::never())->method('storePdf');
        $files->expects(self::never())->method('storeCover');
        $files->expects(self::never())->method('delete');
        $extractor = $this->createMock(PdfTextExtractor::class);
        $extractor->expects(self::once())->method('extract')->with('bad', 20000)->willThrowException(new InvalidPdf);
        $this->expectException(InvalidPdf::class);
        (new UpdateBookInteractor($books, $files, $extractor, $this->unpublishedOutput()))->execute(new UpdateBookInputData(1, 'New', null, 'bad', 'new cover'));
    }

    public function test_database_failure_cleans_only_new_files(): void
    {
        $books = $this->createMock(BookRepository::class);
        $books->method('find')->willReturn(new BookData(1, 'Old', null, 'old.pdf', 'old.jpg'));
        $books->method('save')->willThrowException(new LibraryUnavailable);
        $files = $this->createMock(BookFileStorage::class);
        $files->method('storePdf')->willReturn('new.pdf');
        $files->method('storeCover')->willReturn('new.jpg');
        $deleted = [];
        $files->expects(self::exactly(2))->method('delete')->willReturnCallback(function ($path) use (&$deleted) {
            $deleted[] = $path;
        });
        $extractor = $this->createMock(PdfTextExtractor::class);
        $extractor->method('extract')->willReturn([]);
        try {
            (new UpdateBookInteractor($books, $files, $extractor, $this->unpublishedOutput()))->execute(new UpdateBookInputData(1, 'New', null, 'pdf', 'cover'));
            self::fail('Expected persistence failure.');
        } catch (LibraryUnavailable) {
            self::assertSame(['new.pdf', 'new.jpg'], $deleted);
        }
    }

    private function unpublishedOutput(): UpdateBookOutputBoundary
    {
        $output = $this->createMock(UpdateBookOutputBoundary::class);
        $output->expects(self::never())->method('present');

        return $output;
    }
}
