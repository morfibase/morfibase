<?php

namespace App\Filament\Resources\Tables\Pages;

use App\Filament\Resources\Tables\TableResource;
use App\Helpers\Table\BuilderHelper;
use App\Helpers\Table\TableHelper;
use App\Helpers\Table\Migrations\OneToManyMigration;
use App\Helpers\Table\Migrations\OneToOneMigration;
use App\Helpers\Table\Migrations\RelationshipForeignKey;
use App\Helpers\Table\RelationshipHelper;
use App\Models\GenericModel;
use App\Models\User;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class EditTable extends EditRecord
{
    protected static string $resource = TableResource::class;

    protected array $toDeleteDbColumns = [];

    protected array $toAddDbColumns = [];

    protected array $toDeleteRelationships = [];

    protected ?string $tableName;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        BuilderHelper::generateRandomColumnName($data['schema']);

        /**
         * We need to remove the belongs to relationships as we are not interested in displaying these to the user,
         * we only use them for the system internally
         */

        // Check if the relationships key exists and is an array
        if (isset($data['relationships']) && is_array($data['relationships'])) {
            // Filter out any relationships where the type is 'belongsTo'
            $filteredRelationships = collect($data['relationships'])->filter(function ($relationship) {
                return !isset($relationship['relationship_type']) || $relationship['relationship_type'] != 'belongsTo';
            })->values()->toArray();

            // Overwrite the original relationships with the filtered ones for the form
            $data['relationships'] = $filteredRelationships;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $fields = array_merge($data['display_field'], $data['schema']);
        $this->tableName = TableHelper::uuidToTableName($this->record->id);
        $oldSchema = $this->record->fields();
        $oldSchemaColumnNames = [];

        foreach($oldSchema as $field) {
            $oldSchemaColumnNames[] = $field['data']['db_column_name'];
        }

        // Look for fields that are present in the $oldSchema but not present in the $data (since they were deleted)
        $newSchemaColumnNames = array_map(fn($field) => $field['data']['db_column_name'], $fields);
        $this->toDeleteDbColumns = array_diff($oldSchemaColumnNames, $newSchemaColumnNames);
        $this->toAddDbColumns = BuilderHelper::generateRandomColumnName($data['schema'], $oldSchemaColumnNames);

        /**
         * Because we removed the belongsTo relationship when we filled the relationships form we now must add it
         * back so we don't lose it.
         * 
         * We did this because we don't want to let the user update the belongsTo relationships as this is used only
         * internally.
         */
        $originalRelationships = collect($this->record->relationships ?? []);
        $submittedRelationships = collect($data['relationships'] ?? []);

        $belongsToRelationships = $originalRelationships->filter(function ($relationship) {
            return ($relationship['relationship_type'] ?? null) === 'belongsTo';
        });

        $mergedRelationships = $submittedRelationships->merge($belongsToRelationships);
        $data['relationships'] = $mergedRelationships->toArray();

        return $data;
    }

    public function beforeSave()
    {
        $existingRelationships = isset($this->record['relationships']) ? collect($this->record['relationships']) : collect([]);
        $updatedRelationships = isset($this->data['relationships']) ? collect($this->data['relationships']) : collect([]);

        $existingRelationshipsIds = $existingRelationships->pluck('id');
        $updatedRelationshipsIds = $updatedRelationships->pluck('id');

        $toDeleteRelationshipsIds = $existingRelationshipsIds->diff($updatedRelationshipsIds);
        $this->toDeleteRelationships = $existingRelationships
            ->filter(fn($relationship) => $toDeleteRelationshipsIds->contains($relationship['id']))
            ->toArray();
    }

    public function afterSave()
    {
        try {
            Schema::table($this->tableName, function (Blueprint $table) {
                // Delete columns that are no longer needed (this doesn't include relationship related columns)
                foreach($this->toDeleteDbColumns as $column) {
                    $table->dropColumn($column);
                }

                // Add new columns
                foreach($this->toAddDbColumns as $column) {
                    $table->text($column)->nullable();
                }
            });

            // Add the new fields required for the new relationships
            $this->record->relationships = RelationshipHelper::createRelationships($this->record);
            $this->record->save();

            // Remove the fields assocaited with deleted relationships
            foreach($this->toDeleteRelationships as $relationship) {
                $tableA = $relationship['relationship_a_table'];
                $tableB = $relationship['relationship_b_table'];
                $relationshipType = $relationship['relationship_type'];

                if($relationshipType == 'hasOne') {
                    OneToOneMigration::down($tableB, $tableA);
                } else if($relationshipType == 'hasMany') {
                    OneToManyMigration::down($tableB, $tableA);
                }

                RelationshipHelper::removeBelongsToRelationship($tableB, $tableA);
            }
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title('The table could not be updated because of a database error.')
                ->send();
            
            Log::error([
                'message' => $e->getMessage(),
                'location' => 'EditCollection.php, afterSave() method',
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->halt(shouldRollbackDatabaseTransaction: true);
        }

        $this->js('window.location.reload()');
    }
}
