<?php

namespace App\Helpers\Table;

class TableHelper
{
    public static function uuidToTableName(string $input)
    {
        return 'table_' . str_replace('-', '_', $input);
    }

    public static function uuidToForeignKeyName(string $input)
    {
        return self::uuidToTableName($input) . '_id';
    }

    public static function tableNameToUuid(string $input)
    {
        $uuid = str_replace('table_', '', $input);
        $uuid = str_replace('_', '-', $uuid);
        return $uuid;
    }

    public static function tableNameToForeignKeyName(string $input)
    {
        return $input . '_id';
    }
}