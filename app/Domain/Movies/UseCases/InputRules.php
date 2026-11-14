<?php

namespace App\Domain\Movies\UseCases;

final class InputRules
{
    public static function title(string $title): string
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > 255) {
            throw new \InvalidArgumentException('Enter a movie title of at most 255 characters.');
        }

        return $title;
    }

    public static function region(string $region): string
    {
        $region = strtoupper(trim($region));
        if (! in_array($region, ['ES', 'US', 'GB'], true)) {
            throw new \InvalidArgumentException('Choose a supported region: ES, US or GB.');
        }

        return $region;
    }
}
