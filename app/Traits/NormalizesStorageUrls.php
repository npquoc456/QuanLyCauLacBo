<?php

namespace App\Traits;

trait NormalizesStorageUrls
{
    public static function normalizeStorageUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_contains($value, '/storage/')) {
            $path = parse_url($value, PHP_URL_PATH);

            return $path ?: $value;
        }

        return $value;
    }
}
