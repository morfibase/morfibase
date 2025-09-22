<?php

namespace App\Livewire;

use App\Helpers\Table\FormBuilder;
use App\Helpers\Table\TableBuilder;
use App\Helpers\Table\TableHelper;
use App\Models\GenericModel;
use App\Models\Table as ModelTable;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class GenericTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ModelTable $record;

    public function table(Table $table): Table
    {
        $tableRecordName = ucfirst($this->record->name);
        $relationships = $this->record->relationships;

        return $table
            ->query(GenericModel::genericQuery($this->record))
            ->columns(TableBuilder::generate($this->record))
            ->filters([
                // ...
            ])
            ->recordActions([
                Action::make('View')
                    ->icon('heroicon-m-eye')
                    ->accessSelectedRecords()
                    ->disabledSchema()
                    ->modalSubmitAction(false)
                    ->fillForm(fn ($record) => $this->genericFieldForm($record, $relationships))
                    ->schema(FormBuilder::generate($this->record)),

                Action::make('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->accessSelectedRecords()
                    ->fillForm(fn ($record) => $this->genericFieldForm($record, $relationships))
                    ->schema(FormBuilder::generate($this->record))
                    ->action(function (array $data, GenericModel $record) use ($tableRecordName) {
                        DB::transaction(function () use ($data, $record, $tableRecordName) {
                            $fields = $this->record->fields;
                            $oldData = $record;
                            $newData = $data;
       
                            try {
                                // Creating/Updating/Deleting relationships data
                                if (isset($data['relationship_table_data'])) {
                                    foreach ($data['relationship_table_data'] as $relationshipData) {
                                        foreach ($relationshipData as $tableName => $tableData) {
                                            $tableId = TableHelper::tableNameToUuid($tableName);
                                            $foreignKeyName = TableHelper::uuidToForeignKeyName($this->record->id);
                                            $existingRelationshipRecords = GenericModel::genericQuery($tableId)
                                                ->where($foreignKeyName, '=', $record->id)
                                                ->get();

                                            $submittedIds = collect($tableData)
                                                ->pluck('id')
                                                ->filter()
                                                ->toArray();

                                            /**
                                             * Edge case: If the user removes all the existing record relationships, then the tableData will be empty
                                             * so we will never get to the code bellow to delete the data. Thus we must do it here.
                                             */
                                            if (empty($tableData)) {
                                                foreach ($existingRelationshipRecords as $existingRecord) {
                                                    $existingRecord->delete();
                                                }
                                            }

                                            foreach ($tableData as $fields) {
                                                $isNew = isset($fields['id']) == false;
                                                $fields[TableHelper::uuidToForeignKeyName($this->record->id)] = $record->id;

                                                /**
                                                 * Create a new record
                                                 */
                                                if ($isNew) {
                                                    GenericModel::genericQuery($tableId)
                                                        ->create($fields);
                                                }
                                                /**
                                                 * Update record
                                                 */
                                                else {
                                                    GenericModel::genericQuery($tableId)
                                                        ->where('id', '=', $fields['id'])
                                                        ->update($fields);
                                                }

                                                /**
                                                 * Delete record
                                                 */
                                                $idsToDelete = $existingRelationshipRecords
                                                    ->pluck('id')
                                                    ->diff($submittedIds);

                                                if (! empty($idsToDelete)) {
                                                    GenericModel::genericQuery($tableId)
                                                        ->whereIn('id', $idsToDelete)
                                                        ->delete();
                                                }
                                            }
                                        }
                                    }
                                }

                                // Check if there are file upload fields that were deleted (so we delete the stored files too)
                                foreach($fields as $field) {
                                    if($field['type'] == 'fileUpload') {
                                        $columnName = $field['data']['db_column_name'];
                                        $oldColumnData = json_decode($oldData[$columnName], true);
                                        $newColumnData = $newData[$columnName];

                                        if(is_array($oldColumnData) && is_array($newColumnData)) {
                                            $toDeleteFiles = array_values(array_diff($oldColumnData, $newColumnData));
                                        } else {
                                            $toDeleteFiles = [];
                                        }

                                        foreach($toDeleteFiles as $file) {
                                            $disk = $field['data']['form']['disk'];
                                            Storage::disk($disk)->delete($file);
                                        }
                                    }
                                }

                                unset($data['relationship_table_data']);
                                $record->update($data);
                            } catch (Exception $e) {
                                Log::error([
                                    'message' => $e->getMessage(),
                                    'code' => $e->getCode(),
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine(),
                                ]);

                                Notification::make()
                                    ->title("{$tableRecordName} update failed")
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title("{$tableRecordName} updated successfully")
                                ->success()
                                ->send();
                        });
                    }),

                Action::make('Delete')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->action(function (array $data, GenericModel $record) use ($tableRecordName) {
                        try {
                            $record->delete();
                        } catch (Exception $e) {
                            Log::error([
                                'message' => $e->getMessage(),
                                'code' => $e->getCode(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                            ]);

                            Notification::make()
                                ->title("{$tableRecordName} delete failed")
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title("{$tableRecordName} deleted successfully")
                            ->success()
                            ->send();
                    }),

            ])
            ->toolbarActions([
                // ...
            ]);
    }

    protected function genericFieldForm($record, $relationships)
    {
        $data = $record->toArray();

        /**
         * Some fields get saved as json so we must convert them to array
         */
        foreach($data as &$field) {
            if(json_validate($field)) {
                $field = json_decode($field, true);
            }
        }

        $data['relationship_table_data'] = [];
        foreach ($relationships as $relationship) {
            $relationshipATable = $relationship['relationship_a_table'];
            $relationshipBTable = $relationship['relationship_b_table'];
            $tableAId = TableHelper::tableNameToUuid($relationshipATable);
            $tableBId = TableHelper::tableNameToUuid($relationshipBTable);
            $tableAForeignKeyName = TableHelper::uuidToForeignKeyName($tableAId);

            if (in_array($relationship['relationship_type'], ['hasOne', 'hasMany'])) {
                $data1 = GenericModel::genericQuery($tableBId)
                    ->where($tableAForeignKeyName, '=', $record->id)
                    ->get();

                $data['relationship_table_data'][][$relationshipBTable] = $data1->toArray();
            }
        }

        return $data;
    }

    public function render()
    {
        return view('livewire.generic-table');
    }
}
