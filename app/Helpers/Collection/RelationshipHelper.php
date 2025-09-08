<?php

namespace App\Helpers\Collection;

use App\Helpers\Collection\Migrations\OneToManyMigration;
use App\Helpers\Collection\Migrations\OneToOneMigration;
use App\Models\Collection;
use Illuminate\Support\Str;

class RelationshipHelper
{
    protected static function missingRelationshipData(&$relationship, Collection $collection)
    {
        if (!isset($relationship['id'])) {
            $relationship['id'] = (string) Str::uuid();
        }
            
        $relationship['relationship_a_table'] = CollectionHelper::uuidToTableName($collection->id);
    }

    public static function createRelationships(Collection $collection)
    {
        $newRelationships = $collection->relationships;

        foreach($newRelationships as &$relationship) {
            if($relationship['id'] == null) {
                self::missingRelationshipData($relationship, $collection); 

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
        $collection = Collection::where('id', '=', CollectionHelper::tableNameToUuid($tableA))->first();
        $relationships = $collection->relationships;
        $id = (string) Str::uuid();

        $relationships[] = [
            'id' => $id,
            'relationship_a_table' => $tableA,
            'relationship_type' => 'belongsTo',
            'relationship_b_table' => $tableB,
        ];
        $collection->relationships = $relationships;
        $collection->save();
    }

    public static function removeBelongsToRelationship(string $tableA, string $tableB)
    {
        $collection = Collection::where('id', '=', CollectionHelper::tableNameToUuid($tableA))->first();
        $relationships = $collection->relationships;

        // Filter out the specific relationship to be removed.
        $newRelationships = array_filter($relationships, function ($relationship) use ($tableA, $tableB) {
            return (
                $relationship['relationship_a_table'] === $tableA &&
                $relationship['relationship_type'] === 'belongsTo' &&
                $relationship['relationship_b_table'] === $tableB
            ) == false;
        });

        // Re-index the array after filtering.
        $collection->relationships = array_values($newRelationships);
        $collection->save();
    }
}
