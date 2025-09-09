<?php

namespace App\Helpers\Table;

use App\Helpers\Table\Migrations\OneToManyMigration;
use App\Helpers\Table\Migrations\OneToOneMigration;
use App\Models\Table;
use Illuminate\Support\Str;

class RelationshipHelper
{
    protected static function missingRelationshipData(&$relationship, Table $table)
    {
        if (!isset($relationship['id'])) {
            $relationship['id'] = (string) Str::uuid();
        }
            
        $relationship['relationship_a_table'] = TableHelper::uuidToTableName($table->id);
    }

    public static function createRelationships(Table $table)
    {
        $newRelationships = $table->relationships;

        foreach($newRelationships as &$relationship) {
            if($relationship['id'] == null) {
                self::missingRelationshipData($relationship, $table); 

                // Add relationship to the tables
                if($relationship['relationship_type'] == 'hasOne') {
                    $tableA = $relationship['relationship_a_table'];
                    $tableB = $relationship['relationship_b_table'];

                    // Add to table A the foreign key
                    OneToOneMigration::up($tableB, $tableA);

                    // Update table B that belongs to table A
                    self::addBelongsToRelationship($tableB, $tableA);
                } else if($relationship['relationship_type'] == 'hasMany') {
                    $tableA = $relationship['relationship_a_table'];
                    $tableB = $relationship['relationship_b_table'];

                    // Add to table B the foreign key
                    OneToManyMigration::up($tableB, $tableA);

                    // Update table B that belongs to table A
                    self::addBelongsToRelationship($tableB, $tableA);
                }
            }
        }

        return $newRelationships;
    }

    public static function addBelongsToRelationship(string $tableA, string $tableB)
    {
        $table = Table::where('id', '=', TableHelper::tableNameToUuid($tableA))->first();
        $relationships = $table->relationships;
        $id = (string) Str::uuid();

        $relationships[] = [
            'id' => $id,
            'relationship_a_table' => $tableA,
            'relationship_type' => 'belongsTo',
            'relationship_b_table' => $tableB,
        ];
        $table->relationships = $relationships;
        $table->save();
    }

    public static function removeBelongsToRelationship(string $tableA, string $tableB)
    {
        $table = Table::where('id', '=', TableHelper::tableNameToUuid($tableA))->first();
        $relationships = $table->relationships;

        // Filter out the specific relationship to be removed.
        $newRelationships = array_filter($relationships, function ($relationship) use ($tableA, $tableB) {
            return (
                $relationship['relationship_a_table'] === $tableA &&
                $relationship['relationship_type'] === 'belongsTo' &&
                $relationship['relationship_b_table'] === $tableB
            ) == false;
        });

        // Re-index the array after filtering.
        $table->relationships = array_values($newRelationships);
        $table->save();
    }
}
