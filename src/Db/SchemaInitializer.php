<?php

declare(strict_types=1);

namespace App\Db;

final class SchemaInitializer
{
    public static function run(Connection $connection, string $schemaPath): void
    {
        $sql = file_get_contents($schemaPath);

        if ($sql === false) {
            throw new \RuntimeException("Schema file okunamadi");
        }

        $connection->getPdo()->exec($sql);
    }
}
