<?php

namespace App\Providers;

use App\Domain\Library\UseCases\AddBook\AddBookInputBoundary;
use App\Domain\Library\UseCases\AddBook\AddBookInteractor;
use App\Domain\Library\UseCases\AddBook\AddBookOutputBoundary;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookContentSearch;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;
use App\Domain\Library\UseCases\DataAccessInterfaces\PdfTextExtractor;
use App\Domain\Library\UseCases\DeleteBook\DeleteBookInputBoundary;
use App\Domain\Library\UseCases\DeleteBook\DeleteBookInteractor;
use App\Domain\Library\UseCases\DeleteBook\DeleteBookOutputBoundary;
use App\Domain\Library\UseCases\ListBooks\ListBooksInputBoundary;
use App\Domain\Library\UseCases\ListBooks\ListBooksInteractor;
use App\Domain\Library\UseCases\ListBooks\ListBooksOutputBoundary;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsInputBoundary;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsInteractor;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsOutputBoundary;
use App\Domain\Library\UseCases\SearchBooks\SearchBooksInputBoundary;
use App\Domain\Library\UseCases\SearchBooks\SearchBooksInteractor;
use App\Domain\Library\UseCases\SearchBooks\SearchBooksOutputBoundary;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookInputBoundary;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookInteractor;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookOutputBoundary;
use App\Http\Presenters\Library\AddBookPresenter;
use App\Http\Presenters\Library\DeleteBookPresenter;
use App\Http\Presenters\Library\ListBooksPresenter;
use App\Http\Presenters\Library\SearchBookContentsPresenter;
use App\Http\Presenters\Library\SearchBooksPresenter;
use App\Http\Presenters\Library\UpdateBookPresenter;
use App\Infrastructure\Library\Adapters\EloquentBookContentSearch;
use App\Infrastructure\Library\Adapters\EloquentBookRepository;
use App\Infrastructure\Library\Adapters\LaravelBookFileStorage;
use App\Infrastructure\Library\Adapters\SmalotPdfTextExtractor;
use Illuminate\Support\ServiceProvider;

final class LibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookRepository::class, EloquentBookRepository::class);
        $this->app->bind(BookContentSearch::class, EloquentBookContentSearch::class);
        $this->app->bind(BookFileStorage::class, LaravelBookFileStorage::class);
        $this->app->bind(PdfTextExtractor::class, SmalotPdfTextExtractor::class);
        $this->app->bind(ListBooksInputBoundary::class, ListBooksInteractor::class);
        $this->app->scoped(ListBooksPresenter::class);
        $this->app->bind(ListBooksOutputBoundary::class, fn ($app) => $app->make(ListBooksPresenter::class));
        $this->app->bind(SearchBooksInputBoundary::class, SearchBooksInteractor::class);
        $this->app->scoped(SearchBooksPresenter::class);
        $this->app->bind(SearchBooksOutputBoundary::class, fn ($app) => $app->make(SearchBooksPresenter::class));
        $this->app->bind(AddBookInputBoundary::class, AddBookInteractor::class);
        $this->app->scoped(AddBookPresenter::class);
        $this->app->bind(AddBookOutputBoundary::class, fn ($app) => $app->make(AddBookPresenter::class));
        $this->app->bind(UpdateBookInputBoundary::class, UpdateBookInteractor::class);
        $this->app->scoped(UpdateBookPresenter::class);
        $this->app->bind(UpdateBookOutputBoundary::class, fn ($app) => $app->make(UpdateBookPresenter::class));
        $this->app->bind(DeleteBookInputBoundary::class, DeleteBookInteractor::class);
        $this->app->scoped(DeleteBookPresenter::class);
        $this->app->bind(DeleteBookOutputBoundary::class, fn ($app) => $app->make(DeleteBookPresenter::class));
        $this->app->bind(SearchBookContentsInputBoundary::class, SearchBookContentsInteractor::class);
        $this->app->scoped(SearchBookContentsPresenter::class);
        $this->app->bind(SearchBookContentsOutputBoundary::class, fn ($app) => $app->make(SearchBookContentsPresenter::class));
    }
}
