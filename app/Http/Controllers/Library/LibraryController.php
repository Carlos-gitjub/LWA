<?php

namespace App\Http\Controllers\Library;

use App\Domain\Library\UseCases\AddBook\AddBookInputBoundary;
use App\Domain\Library\UseCases\AddBook\AddBookInputData;
use App\Domain\Library\UseCases\BookNotFound;
use App\Domain\Library\UseCases\DeleteBook\DeleteBookInputBoundary;
use App\Domain\Library\UseCases\DeleteBook\DeleteBookInputData;
use App\Domain\Library\UseCases\InvalidPdf;
use App\Domain\Library\UseCases\LibraryUnavailable;
use App\Domain\Library\UseCases\ListBooks\ListBooksInputBoundary;
use App\Domain\Library\UseCases\ListBooks\ListBooksInputData;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsInputBoundary;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsInputData;
use App\Domain\Library\UseCases\SearchBooks\SearchBooksInputBoundary;
use App\Domain\Library\UseCases\SearchBooks\SearchBooksInputData;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookInputBoundary;
use App\Domain\Library\UseCases\UpdateBook\UpdateBookInputData;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Library\AddBookPresenter;
use App\Http\Presenters\Library\DeleteBookPresenter;
use App\Http\Presenters\Library\ListBooksPresenter;
use App\Http\Presenters\Library\SearchBookContentsPresenter;
use App\Http\Presenters\Library\SearchBooksPresenter;
use App\Http\Presenters\Library\UpdateBookPresenter;
use App\Http\Requests\Library\SaveBookRequest;
use App\Http\Requests\Library\SearchBooksRequest;
use Illuminate\Http\Request;

final class LibraryController extends Controller
{
    public function index(Request $request, ListBooksInputBoundary $useCase, ListBooksPresenter $presenter)
    {
        return $this->respond(function () use ($request, $useCase, $presenter) {
            $useCase->execute(new ListBooksInputData);

            return $request->wantsJson() ? response()->json($presenter->viewModel->data)
                : inertia('Library/Index', ['books' => $presenter->viewModel->data]);
        });
    }

    public function search(SearchBooksRequest $request, SearchBooksInputBoundary $useCase, SearchBooksPresenter $presenter)
    {
        return $this->respond(function () use ($request, $useCase, $presenter) {
            $useCase->execute(new SearchBooksInputData((string) $request->input('q', '')));

            return response()->json($presenter->viewModel->data);
        });
    }

    public function store(SaveBookRequest $request, AddBookInputBoundary $useCase, AddBookPresenter $presenter)
    {
        if (! $request->hasFile('file')) {
            return response()->json(['error' => 'Archivo no válido'], 400);
        }

        return $this->respond(function () use ($request, $useCase, $presenter) {
            $data = $request->validated();
            $useCase->execute(new AddBookInputData($data['title'], $data['author'] ?? null, $request->file('file')->get(), $this->cover($request)));

            return response()->json($presenter->viewModel->data);
        });
    }

    public function update(SaveBookRequest $request, UpdateBookInputBoundary $useCase, UpdateBookPresenter $presenter, string $id)
    {
        return $this->respond(function () use ($request, $useCase, $presenter, $id) {
            $data = $request->validated();
            $useCase->execute(new UpdateBookInputData((int) $id, $data['title'], $data['author'] ?? null, $request->file('file')?->get(), $this->cover($request)));

            return response()->json($presenter->viewModel->data);
        });
    }

    public function destroy(Request $request, DeleteBookInputBoundary $useCase, DeleteBookPresenter $presenter, string $id)
    {
        return $this->respond(function () use ($useCase, $presenter, $id) {
            $useCase->execute(new DeleteBookInputData((int) $id));

            return response()->json($presenter->viewModel->data);
        });
    }

    public function searchEngine(SearchBooksRequest $request, SearchBookContentsInputBoundary $useCase, SearchBookContentsPresenter $presenter)
    {
        return $this->respond(function () use ($request, $useCase, $presenter) {
            $useCase->execute(new SearchBookContentsInputData((string) $request->input('q', ''), $request->input('authors', []), array_map('intval', $request->input('books', []))));

            return response()->json($presenter->viewModel->data);
        });
    }

    private function cover(SaveBookRequest $request): ?string
    {
        if (! $request->filled('cover_base64')) {
            return null;
        }
        $encoded = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('cover_base64'));
        $contents = base64_decode($encoded, true);
        if ($contents === false) {
            throw new \InvalidArgumentException('Invalid cover image encoding.');
        }

        return $contents;
    }

    private function respond(callable $operation)
    {
        try {
            return $operation();
        } catch (BookNotFound) {
            return response()->json(['message' => 'Book not found.'], 404);
        } catch (InvalidPdf) {
            return response()->json(['error' => 'Error al procesar el PDF'], 422);
        } catch (\InvalidArgumentException $error) {
            return response()->json(['message' => $error->getMessage()], 422);
        } catch (LibraryUnavailable $error) {
            report($error);

            return response()->json(['message' => 'Could not complete the library operation. Please try again.'], 503);
        }
    }
}
