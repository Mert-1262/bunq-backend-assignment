<?php

declare(strict_types=1);

namespace Tests\Helpers;

final class TestDb
{
    public static function path(): string
    {
        return __DIR__ . '/../../storage/test.sqlite';
    }

    public static function resetFile(): void
    {
        $path = self::path();

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        touch($path);
    }
}
