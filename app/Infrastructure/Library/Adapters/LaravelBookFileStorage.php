<?php

namespace App\Infrastructure\Library\Adapters;

use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\LibraryUnavailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class LaravelBookFileStorage implements BookFileStorage
{
    public function storePdf(string $contents): string
    {
        return $this->put('books/'.Str::uuid().'.pdf', $contents);
    }

    public function storeCover(string $contents): string
    {
        return $this->put('covers/'.Str::uuid().'.jpg', $contents);
    }

    private function put(string $path, string $contents): string
    {
        try {
            if (! Storage::disk('public')->put($path, $contents)) {
                throw new \RuntimeException('File write failed.');
            }

            return $path;
        } catch (\Throwable $error) {
            $this->delete($path);
            throw new LibraryUnavailable('Book file storage unavailable.', 0, $error);
        }
    }

    public function delete(?string $path): void
    {
        if ($path === null) {
            return;
        }
        try {
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('Library file cleanup failed.', ['path' => $path]);
            }
        } catch (\Throwable $error) {
            Log::warning('Library file cleanup failed.', ['path' => $path, 'exception' => $error]);
        }
    }
}
