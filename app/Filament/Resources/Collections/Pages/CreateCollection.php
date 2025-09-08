<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Helpers\Collection\BuilderHelper;
use App\Helpers\Collection\CollectionHelper;
use App\Helpers\Collection\MigrationHelper;
use App\Helpers\Collection\Migrations\OneToManyMigration;
use App\Helpers\Collection\Migrations\OneToOneMigration;
use App\Helpers\Collection\RelationshipHelper;
use App\Models\Collection;
use App\Models\GenericModel;
use App\Models\User;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    public function afterCreate()
    {
        try {
            $tableName = CollectionHelper::uuidToTableName($this->record->id); 
            $schema = $this->record->schema;

            Schema::create($tableName, function (Blueprint $table) use ($schema) {
                $table->id();

                foreach($schema as $field) {
                    $table->text($field['data']['db_column_name'])->nullable();
                }
            });

            $this->record->relationships = RelationshipHelper::createRelationships($this->record);
            $this->record->save();
        } catch (Exception $e) {
            $this->record->delete();
            
            Notification::make()
                ->danger()
                ->title('The collection could not be created because of a database error.')
                ->send();

            Log::error([
                'message' => $e->getMessage(),
                'location' => 'CreateCollection.php, afterCreate() method',
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            $this->halt(shouldRollbackDatabaseTransaction: true);
        }

        $this->js('window.location.reload()');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {   
        BuilderHelper::generateRandomColumnName($data['schema']);

        return $data;
    }
}
