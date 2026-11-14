<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsOutputBoundary;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsOutputData;

final class SearchBookContentsPresenter implements SearchBookContentsOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(SearchBookContentsOutputData $output): void
    {
        $results = [];
        foreach ($output->results as $result) {
            $page = $result->page;
            $content = $page->content;
            $pos = $output->query === '' ? false : mb_stripos($content, $output->query);
            $preview = $pos === false ? mb_substr($content, 0, 80).'...'
                : '...'.mb_substr($content, max(0, $pos - 40), mb_strlen($output->query) + 80).'...';
            $results[] = ['id' => $page->id, 'book' => $page->book->title,
                'author' => $page->book->author ?? 'Autor desconocido', 'file_path' => $page->book->filePath,
                'preview' => $preview, 'page' => $page->number, 'count' => $result->count];
        }
        $data = ['results' => $results, 'total_matches' => $output->totalMatches, 'total_pages' => count($results)];
        $this->viewModel = new ViewModel($data);
    }
}
