<?php

namespace App\Filament\Resources\Tables\Pages;

use App\Filament\Resources\Tables\TableResource;
use App\Helpers\Table\TableHelper;
use App\Helpers\Table\FormBuilder;
use App\Models\Table;
use App\Models\GenericModel;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViewTable extends ViewRecord
{
    protected static string $resource = TableResource::class;

    public function getHeaderActions(): array
    {
        return [
            Action::make('create_new_table_record')
                ->icon('heroicon-m-document-plus')
                ->label('Create new ' . strtolower($this->record->name))
                ->schema(FormBuilder::generate($this->record))
                ->action(function($data) {
                    DB::transaction(function () use ($data) {
                        $attributes = [];
                        $relationships = $this->record->relationships;
                        $schema = $this->record->fields();
                        $attributes['id'] = (string) Str::uuid();
                        $toCreateRelationshipRecords = [];
                        /**
                         * Add relation related fields to the attributes array
                         */
                        foreach($relationships as $relationship) {
                            $relationshipType = $relationship['relationship_type'];
                            $tableARelation = $relationship['relationship_a_table'];
                            $tableBRelation = $relationship['relationship_b_table'];
                            $tableAId = TableHelper::tableNameToUuid($tableARelation);
                            $tableBId = TableHelper::tableNameToUuid($tableBRelation);
                            $tableB = Table::where('id', '=', $tableBId)->first();
                            $tableAForeignKeyName = TableHelper::tableNameToForeignKeyName($tableARelation);
                            $tableBForeignKeyName = TableHelper::tableNameToForeignKeyName($tableBRelation);

                            if ($relationshipType == 'belongsTo') {
                                if(isset($data[$tableBForeignKeyName])) {
                                    $attributes[$tableBForeignKeyName] = $data[$tableBForeignKeyName];
                                }
                            } else if(in_array($relationshipType, ['hasOne', 'hasMany'])) {
                                foreach($data['relationship_table_data'] as &$relationshipTables) {
                                    foreach($relationshipTables as $tableName => &$relationshipData) {
                                        foreach($relationshipData as &$tableColumns) {
                                            $tableColumns[$tableAForeignKeyName] = $attributes['id'];
                                            
                                            // We can't create it here since the main record is not yet created
                                            $toCreateRelationshipRecords[] = [
                                                'table' => $tableB,
                                                'data' => $tableColumns
                                            ];
                                        }                                        
                                    }
                                }
                            }
                        }

                        foreach($schema as $field) {
                            if(isset($data[$field['data']['db_column_name']])) {
                                $attributes[$field['data']['db_column_name']] = $data[$field['data']['db_column_name']];
                            }
                        }

                        foreach($attributes as &$attribute) {
                            if(is_array($attribute)) {
                                $attribute = json_encode($attribute);
                            }
                        }

                        GenericModel::genericQuery($this->record)
                            ->create($attributes);

                        foreach($toCreateRelationshipRecords as $relationshipRecord) {
                            GenericModel::genericQuery($relationshipRecord['table'])
                                ->create($relationshipRecord['data']);
                        }

                        $this->js('window.location.reload()'); 
                    });
                })
        ];
    }
}
