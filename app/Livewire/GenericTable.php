<?php

namespace App\Livewire;

use App\Helpers\Table\FormBuilder;
use App\Helpers\Table\TableBuilder;
use App\Helpers\Table\TableHelper;
use App\Models\Table as ModelTable;
use App\Models\GenericModel;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use App\Models\Table as TableModel; 
use Illuminate\Support\Facades\DB;

class GenericTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

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
                Action::make('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->accessSelectedRecords()
                    ->fillForm(function ($record) use ($relationships) {
                        $data = $record->toArray();
                        $data['relationship_table_data'] = [];
                        foreach($relationships as $relationship) {
                            $relationshipATable = $relationship['relationship_a_table'];
                            $relationshipBTable = $relationship['relationship_b_table'];
                            $tableAId = TableHelper::tableNameToUuid($relationshipATable);
                            $tableBId = TableHelper::tableNameToUuid($relationshipBTable);
                            $tableAForeignKeyName = TableHelper::uuidToForeignKeyName($tableAId);

                            if(in_array($relationship['relationship_type'], ['hasOne', 'hasMany'])) {
                                $data1 = GenericModel::genericQuery($tableBId)
                                    ->where($tableAForeignKeyName, '=', $record->id)
                                    ->get();
                
                                $data['relationship_table_data'][][$relationshipBTable] = $data1->toArray();                        
                            }
                        }

                        return $data;
                    })
                    ->schema(FormBuilder::generate($this->record))
                    ->action(function(array $data, GenericModel $record) use ($tableRecordName) {
                        DB::transaction(function () use ($data, $record, $tableRecordName) {
                            try {
                                if(isset($data['relationship_table_data'])) {
                                    foreach($data['relationship_table_data'] as $relationshipData) {
                                        foreach($relationshipData as $tableName => $tableData) {
                                            $tableId = TableHelper::tableNameToUuid($tableName);

                                            foreach($tableData as $fields) {
                                                $isNew = isset($fields['id']) == false;

                                                // The record exists in the db and we now have to update it
                                                if($isNew == false) {
                                                    GenericModel::genericQuery($tableId)
                                                        ->where('id', '=', $fields['id'])
                                                        ->update($fields);
                                                }
                                            }
                                        }
                                    }
                                }
                                
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
                    ->action(function(array $data, GenericModel $record) use ($tableRecordName) {
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
                    })
                    
            ])
            ->toolbarActions([
                // ...
            ]);
    }

    // protected function extractTableSearchWords(string $search): array
    // {
    //     return [ $search ];
    // }

    public function render()
    {
        return view('livewire.generic-table');
    }
}
