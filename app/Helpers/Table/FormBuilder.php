<?php

namespace App\Helpers\Table;

use App\Helpers\Table\Callbacks\Callbacks;
use App\Helpers\Table\Constants\Constants;
use App\Helpers\Table\Enums\FormAction;
use App\Models\GenericModel;
use App\Models\Table;

class FormBuilder
{
    public static function generate(Table $table, FormAction $formAction = FormAction::Create): array
    {
        $classReferences = Constants::FORM_CLASS_REFERENCES;
        $form = [];
        $schema = $table->schema;
        $relationships = $table->relationships;
        $displayField = $table->display_field;

        foreach ($displayField as $field) {
            $fieldType = $field['type'] ?? null;
            $fieldData = $field['data'] ?? null;

            if($fieldType && $fieldData) {
                $form[] = self::genericField($fieldType, $classReferences[$fieldType], $fieldData, $formAction);
            }
        }

        foreach ($schema as $field) {
            $fieldType = $field['type'] ?? null;
            $fieldData = $field['data'] ?? null;

            if($fieldType && $fieldData) {
                $form[] = self::genericField($fieldType, $classReferences[$fieldType], $fieldData, $formAction);
            }
        }

        foreach ($relationships as $relationship) {
            if($relationship['relationship_type'] == 'belongsTo') {
                $relationshipBTable = $relationship['relationship_b_table'];
                $tableBId = TableHelper::tableNameToUuid($relationshipBTable);
                $tableB = Table::where('id', '=', $tableBId)->first();

                $options = GenericModel::genericQuery($tableB)->get()->keyBy('id');
                $form[] = self::genericField('select', $classReferences['select'], [
                    'db_column_name' => TableHelper::tableNameToForeignKeyName($relationshipBTable),
                    'label' => $tableB->name,
                    'options' => $options,
                    'native' => false
                ], $formAction);
            }
        }

        return $form;
    }

    public static function genericField(string $fieldType, string $reference, array $fieldData, FormAction $formAction)
    {
        $inputTypeCallbacks = Callbacks::{$fieldType}();

        $input = $reference::make($fieldData['db_column_name'] ?? BuilderHelper::getNameFromLabel($fieldData['label']));

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