<?php

namespace App\Helpers\Table;

use App\Filament\Tables\Columns\FileCountColumn;
use App\Helpers\Table\Callbacks\TableCallbacks;
use App\Helpers\Table\Constants\Constants;
use App\Models\GenericModel;
use App\Models\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;

class TableBuilder
{
    public static function generate(Table $table): array
    {
        $classReferences = Constants::TABLE_CLASS_REFERENCES;
        $tableColumns = [];
        // Display field + schema
        $schema = $table->fields();
        $relationships = $table->relationships;
        $formToTableMap = Constants::FORM_TO_TABLE_MAP;

        foreach ($schema as $field) {
            $fieldType = $field['type'] ?? null;

            if ($fieldType != null && isset($formToTableMap[$fieldType])) {
                $columnType = $formToTableMap[$fieldType];
            } else {
                $columnType = null;
            }
            
            $columnData = $field['data'] ?? null;
            $columnLabel = $field['data']['label'] ?? null;
            $dbColumnName = $field['data']['db_column_name'] ?? null;

            if ($columnType && $columnData) {
                $tableColumns[] = self::genericColumn($dbColumnName, $columnLabel, $columnType, $classReferences[$columnType], $columnData);
            }
        }

        return $tableColumns;
    }

    public static function genericColumn(string $dbColumnName, string $columnLabel, string $columnType, string $reference, array $columnData)
    {
        $columnTypeCallbacks = TableCallbacks::{$columnType}();
        $input = $reference::make($dbColumnName ?? BuilderHelper::getNameFromLabel($columnLabel))
            ->label($columnLabel);

        if (isset($columnData['view']['table'])) {
            foreach ($columnData['view']['table'] as $option => $params) {
                if (isset($columnTypeCallbacks[$option]) && isset($params)) {
                    $columnTypeCallbacks[$option]($input, [$params]);
                }
            }
        }

        return $input;
    }

    public static function sharedProperties(array $field, Column &$column)
    {
        if (isset($field['sortable']) && $field['sortable'] == true) {
            $column->sortable();
        }

        if (isset($field['searchable']) && $field['searchable']) {
            $column->searchable();

            // $column->searchable(isIndividual:true, query: function (Builder $query, string $search) use ($field): Builder  {
            //     return $query
            //         ->whereBlind($field['db_column_name'], "{$field['db_column_name']}_bi", $search);
            // });
        }

        if (isset($field['toggleable']) && $field['toggleable'] == true) {
            $column->toggleable();
        }

        if (isset($field['tooltip']) && $field['tooltip']) {
            $column->tooltip($field['tooltip']);
        }
    }

    public static function selectColumn($fieldData)
    {
        if (isset($fieldData['multiple']) && $fieldData['multiple'] == false) {
            $column = SelectColumn::make($fieldData['db_column_name'])
                ->native(false)
                ->placeholder('No data')
                ->label($fieldData['label'])
                ->afterStateUpdated(function ($record, $state) {
                    Notification::make()
                        ->success()
                        ->title('Data updated successfully')
                        ->send();
                });

            if ($fieldData['enable_relationship'] == false) {
                $column->options($fieldData['options']);
            }
        } else {
            $column = TextColumn::make($fieldData['db_column_name'])
                ->label($fieldData['label'])
                ->formatStateUsing(function (string $state, GenericModel $record) {
                    if (! is_string($state)) {
                        return $state;
                    }

                    $decoded = json_decode($state);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return implode(', ', $decoded);
                    }

                    return $state;

                });
        }

        self::sharedProperties($fieldData, $column);

        return $column;
    }

    public static function textInputColumn($fieldData)
    {
        $column = TextInputColumn::make($fieldData['db_column_name'])
            ->placeholder('No data')
            ->label($fieldData['label'])
            ->afterStateUpdated(function ($record, $state) {
                Notification::make()
                    ->success()
                    ->title('Data updated successfully')
                    ->send();
            });

        self::sharedProperties($fieldData, $column);

        return $column;
    }

    public static function fileCountColumn($fieldData)
    {
        $column = FileCountColumn::make($fieldData['db_column_name'])
            ->placeholder('No data')
            ->label($fieldData['label'])
            ->afterStateUpdated(function ($record, $state) {
                Notification::make()
                    ->success()
                    ->title('Data updated successfully')
                    ->send();
            });

        self::sharedProperties($fieldData, $column);

        return $column; 
    }
}
