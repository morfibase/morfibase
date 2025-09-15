<?php

namespace App\Helpers\Table;

use App\Helpers\Table\Callbacks\FormCallbacks;
use App\Helpers\Table\Constants\Constants;
use App\Helpers\Table\Enums\FormAction;
use App\Models\GenericModel;
use App\Models\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

use function Illuminate\Log\log;

class FormBuilder
{
    public static function generate(Table $table, FormAction $formAction = FormAction::Create): array
    {
        $classReferences = Constants::FORM_CLASS_REFERENCES;
        $form = [];
        $schema = $table->fields();
        $relationships = $table->relationships;

        foreach ($schema as $field) {
            $fieldType = $field['type'] ?? null;
            $fieldData = $field['data']['form'] ?? null;
            $fieldLabel = $field['data']['label'] ?? null;
            $dbColumnName = $field['data']['db_column_name'] ?? null;

            if($fieldType && $fieldData && $fieldLabel && $dbColumnName) {
                $form[] = self::genericField($dbColumnName, $fieldLabel, $fieldType, $classReferences[$fieldType], $fieldData, $formAction);
            }
        }

        foreach ($relationships as $relationship) {
            $relationshipId = $relationship['id'];
            $relationshipATable = $relationship['relationship_a_table'];
            $relationshipBTable = $relationship['relationship_b_table'];
            $tableBId = TableHelper::tableNameToUuid($relationshipBTable);
            $tableB = Table::where('id', '=', $tableBId)->first();
            $tableAForeignKeyName = TableHelper::tableNameToForeignKeyName($relationshipATable);
            $relationshipType = $relationship['relationship_type'];
            $relationshipTableData = [];

            if($relationshipType == 'belongsTo') {
                $options = GenericModel::genericQuery($tableB)
                    ->get()
                    ->pluck($tableB->display_field[0]['data']['db_column_name'], 'id');

                if($options->count() > 0) {
                    $form[] = self::genericField(TableHelper::tableNameToForeignKeyName($relationshipBTable), $tableB->name, 'select', $classReferences['select'], [
                        'options' => $options,
                        'native' => false,
                        'searchable' => true
                    ], $formAction);
                }
            } else if(in_array($relationshipType, ['hasOne', 'hasMany'])) {
                $fields = $tableB->fields();
                $repeaterFields = [];

                // Render fields from the relationship table
                foreach ($fields as $field) {
                    $fieldType = $field['type'] ?? null;
                    $fieldData = $field['data']['form'] ?? null;
                    $fieldLabel = $field['data']['label'] ?? null;
                    $dbColumnName = $field['data']['db_column_name'] ?? null;


                    // This has to be set to the value of the new table A id (the new record that we create)
                    $repeaterFields[] = Hidden::make($tableAForeignKeyName)->default(null);

                    if($fieldType && $fieldData && $fieldLabel && $dbColumnName) {
                        $repeaterFields[] = self::genericField($dbColumnName, $fieldLabel, $fieldType, $classReferences[$fieldType], $fieldData, $formAction);
                    }
                }

                $relationshipTableData[] = Repeater::make($relationshipBTable)
                    ->label($tableB->name)
                    ->default([])
                    ->reorderable(false)
                    ->maxItems(fn() => $relationshipType == 'hasOne' ? 1 : null)
                    ->schema($repeaterFields);

                if(empty($relationshipTableData) == false) {
                    $form[] = Repeater::make('relationship_table_data')
                        ->hiddenLabel()
                        ->reorderable(false)
                        ->deletable(false)
                        ->addable(false)
                        ->schema($relationshipTableData);
                }
            }
        }

        return $form;
    }

    public static function genericField(string $dbColumnName, string $fieldLabel, string $fieldType, string $reference, array $fieldData, FormAction $formAction)
    {
        $inputTypeCallbacks = FormCallbacks::{$fieldType}();
        $input = $reference::make($dbColumnName ?? BuilderHelper::getNameFromLabel($fieldLabel))
            ->label($fieldLabel);

        foreach($fieldData as $option => $params) {
            if(isset($inputTypeCallbacks[$option]) && isset($params)) {
                $inputTypeCallbacks[$option]($input, [$params]);
            }
        }

        if(
            $formAction == FormAction::Edit &&
            isset($fieldData['enable_relationship']) && 
            $fieldData['enable_relationship'] == false && 
            $fieldType == 'select'
        ) {
            $input->formatStateUsing(function (string $state) use ($fieldData): string | array {
                $multiple = isset($fieldData['multiple']) && $fieldData['multiple'] == true;

                if (!is_string($state)) {
                    return $state;
                }

                $decoded = json_decode($state);

                if (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
                    if($multiple) {
                        return $decoded;
                    } else {
                        return implode(', ', $decoded);
                    }
                }

                if($multiple) {
                    return [ $state ];
                } else {
                    return $state;
                }
            });
        }

        return $input;
    }
}