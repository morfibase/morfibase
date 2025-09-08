<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Helpers\Collection\FormBuilder;
use App\Models\GenericModel;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCollection extends ViewRecord
{
    protected static string $resource = CollectionResource::class;

    public function getHeaderActions(): array
    {
        return [
            Action::make('create_new_collection_item')
                ->icon('heroicon-m-document-plus')
                ->label('Create new ' . strtolower($this->record->name))
                ->schema(FormBuilder::generate($this->record))
                ->action(function($data) {
                    $attributes = [];
                    $relationships = $this->record->relationships;
                    $hasRelationship = false;

                    foreach($relationships as $relationship) {
                        if(in_array($relationship['relationship_type'], ['hasOne', 'hasMany'])) {
                            $hasRelationship = true;
                        }
                    }

                    if(empty($this->record->schema) == false || $hasRelationship) {
                        foreach($this->record->schema as $field) {
                            $attributes[$field['data']['db_column_name']] = $data[$field['data']['db_column_name']];
                        }

                        foreach($attributes as &$attribute) {
                            if(is_array($attribute)) {
                                $attribute = json_encode($attribute);
                            }
                        }

                        GenericModel::genericQuery($this->record)
                            ->create($attributes);

                        $this->js('window.location.reload()'); 
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('This collection has no fields defined.')
                            ->body('In order to create a new collection resource you must first define the collection fields.')
                            ->send();
                    }
                })
        ];
    }
}
