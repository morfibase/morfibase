<?php

namespace App\Livewire;

use App\Helpers\Collection\FormBuilder;
use App\Helpers\Collection\TableBuilder;
use App\Models\Collection;
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

class GenericCollectionTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public Collection $record;

    public function table(Table $table): Table
    {
        $collectionName = ucfirst($this->record->name);

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
                    ->fillForm(fn(GenericModel $record) => $record->toArray())
                    ->schema(FormBuilder::generate($this->record))
                    ->action(function(array $data, GenericModel $record) use ($collectionName) {
                        foreach ($data as $key => $value) {
                            $record->{$key} = $value;
                        }

                        try {
                            $record->save();
                        } catch (Exception $e) {
                            Log::error([
                                'message' => $e->getMessage(),
                                'code' => $e->getCode(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                            ]);

                            Notification::make()
                                ->title("{$collectionName} update failed")
                                ->danger()
                                ->send();
                            
                            return;
                        }

                        Notification::make()
                            ->title("{$collectionName} updated successfully")
                            ->success()
                            ->send();                    
                    }),

                Action::make('Delete')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->action(function(array $data, GenericModel $record) use ($collectionName) {
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
                                ->title("{$collectionName} delete failed")
                                ->danger()
                                ->send();
                            
                            return;
                        }

                        Notification::make()
                            ->title("{$collectionName} deleted successfully")
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
        return view('livewire.generic-collection-table');
    }
}
