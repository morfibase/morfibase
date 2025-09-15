<?php

namespace App\Helpers\Table\Migrations;

class MigrationHelper
{
    public static function generateForeignKeyName(string $tableA, string $tableB, string $columnName): string
    {
        // Combine table names + column, hash, and take first 16 chars to avoid MySQL limit
        $hash = substr(sha1($tableA . '_' . $tableB . '_' . $columnName), 0, 16);

        return "fk_{$hash}";
    }
}