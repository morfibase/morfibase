<?php

namespace App\Helpers\Collection;

use Illuminate\Support\Str;

class BuilderHelper
{
    public static function getNameFromLabel($label) 
    {
        return strtolower(str_replace(' ', '_', $label));
    }

    public static function generateRandomColumnName(array &$schema, $usedColumnNames = []): array
    {
        $toBeAddedColumns = [];

        foreach($schema as &$field) {
            if($field['data']['db_column_name'] == null) {
                do {
                    $columnName = 'col_' . Str::random(6);
                } while(in_array($columnName, $usedColumnNames) == true);

                $field['data']['db_column_name'] = $columnName;
                $usedColumnNames[] = $columnName;
                // We must do it like this because some of the columns might come from the parameter
                $toBeAddedColumns[] = $columnName;
            }
        }

        return $toBeAddedColumns;
    }
}