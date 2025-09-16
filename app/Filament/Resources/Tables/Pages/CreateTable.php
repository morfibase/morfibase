<?php

namespace App\Filament\Resources\Tables\Pages;

use App\Filament\Resources\Tables\TableResource;
use App\Helpers\Table\BuilderHelper;
use App\Helpers\Table\RelationshipHelper;
use App\Helpers\Table\TableHelper;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateTable extends CreateRecord
{
    protected static string $resource = TableResource::class;

    public function afterCreate()
    {
        try {
            $tableName = TableHelper::uuidToTableName($this->record->id);
            $schema = $this->record->fields();

            Schema::create($tableName, function (Blueprint $table) use ($schema) {
                $table->uuid('id')->primary();

                foreach ($schema as $field) {
                    $table->longText($field['data']['db_column_name'])->nullable();
                }
            });

            $this->record->relationships = RelationshipHelper::createRelationships($this->record);
            $this->record->save();
        } catch (Exception $e) {
            $this->record->delete();

            Notification::make()
                ->danger()
                ->title('The table could not be created because of a database error.')
                ->send();

            Log::error([
                'message' => $e->getMessage(),
                'location' => 'CreateTable.php, afterCreate() method',
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
        BuilderHelper::generateRandomColumnName($data['display_field']);
        BuilderHelper::generateRandomColumnName($data['fields']);

        return $data;
    }
}
