<?php

namespace App\Helpers\Collection;

use App\Helpers\Collection\Constants\Constants;
use App\Models\Collection;
use App\Models\GenericModel;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;

use function Illuminate\Log\log;

class TableBuilder
{
    public static function generate(Collection $collection): array
    {
        $form = [];
        $schema = $collection->schema;
        $formToTableMap = Constants::FORM_TO_TABLE_MAP;
        foreach ($schema as $field) {
            $formFieldType = $field['type'];
            $tableFieldType = $formToTableMap[$formFieldType];
            $fieldData = $field['data'];

            $form[] = self::$tableFieldType($fieldData);
        }

        return $form;
    }

    public static function sharedProperties(array $field, Column &$column)
    {
        if(isset($field['sortable']) && $field['sortable'] == true) {
            $column->sortable();
        }

        if(isset($field['searchable']) && $field['searchable']) {
            $column->searchable();

            // $column->searchable(isIndividual:true, query: function (Builder $query, string $search) use ($field): Builder  {
            //     return $query
            //         ->whereBlind($field['db_column_name'], "{$field['db_column_name']}_bi", $search);
            // });
        }

        if(isset($field['toggleable']) && $field['toggleable'] == true) {
            $column->toggleable();
        }

        if(isset($field['tooltip']) && $field['tooltip']) {
            $column->tooltip($field['tooltip']);
        }
    }

    public static function selectColumn($fieldData)
    {
        if(isset($fieldData['multiple']) && $fieldData['multiple'] == false) {
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

            if($fieldData['enable_relationship'] == false) {
                $column->options($fieldData['options']);
            }
        } else {
            $column = TextColumn::make($fieldData['db_column_name'])
                ->label($fieldData['label'])
                ->formatStateUsing(function (string $state, GenericModel $record) {
                    if (!is_string($state)) {
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
}