<?php

namespace App\Domain\Library\UseCases\DataAccessInterfaces;

interface BookFileStorage
{
    public function storePdf(string $contents): string;

    public function storeCover(string $contents): string;

    /** Best-effort cleanup: implementations log failures without masking a completed operation. */
    public function delete(?string $path): void;
}
