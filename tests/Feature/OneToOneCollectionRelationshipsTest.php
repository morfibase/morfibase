<?php

use App\Filament\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Tables\Pages\EditCollection;
use App\Filament\Resources\Tables\TableResource;
use App\Helpers\Table\TableHelper;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function Pest\Livewire\livewire;

// Create
it('one to one relationship saved corectly in the relationship json field as well as migrated correctly', function () {
    /**
     * We must check if the one to one relationship has both a "hasOne" as well as "belongsTo" relation.
     */
    $profile = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Profile',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Client',
            'relationships' => [
               [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasOne',
                    'relationship_b_table' => TableHelper::uuidToTableName($profile->record->id)
               ]
            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $clientCollection = Table::where('name', '=', 'Client')->first();
    $profileCollection = Table::where('name', '=', 'Profile')->first();

    $clientTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $profileTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($profileCollection->id) . ')');

    /**
     * Check if the data was saved correctly from a migration point of view
     */
    expect(count($clientTableSchema))->toEqual(1);
    expect(count($profileTableSchema))->toEqual(2);
    
    expect($clientTableSchema[0]->name)->toEqual('id');
    expect($clientTableSchema[0]->type)->toEqual('INTEGER');
    expect($clientTableSchema[0]->notnull)->toEqual(1);
    expect($clientTableSchema[0]->dflt_value)->toBeNull();
    expect($clientTableSchema[0]->pk)->toEqual(1);

    expect($profileTableSchema[0]->name)->toEqual('id');
    expect($profileTableSchema[0]->type)->toEqual('INTEGER');
    expect($profileTableSchema[0]->notnull)->toEqual(1);
    expect($profileTableSchema[0]->dflt_value)->toBeNull();
    expect($profileTableSchema[0]->pk)->toEqual(1);

    expect($profileTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($profileTableSchema[1]->type)->toEqual('INTEGER');
    expect($profileTableSchema[1]->notnull)->toEqual(1);
    expect($profileTableSchema[1]->dflt_value)->toBeNull();
    expect($profileTableSchema[1]->pk)->toEqual(0);

    $clientTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $profileTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($profileCollection->id) . ')');

    expect(count($clientTableForeignKeyList))->toEqual(0);
    expect(count($profileTableForeignKeyList))->toEqual(1);
    
    expect($profileTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($profileTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($profileTableForeignKeyList[0]->to)->toEqual('id');
    expect($profileTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

    /**
     * Check if the data was saved correctly from a JSON point of view
     */
    expect($clientCollection)->not->toBeNull();
    expect($profileCollection)->not->toBeNull();

    $clientRelationships = $clientCollection->relationships;
    $profileRelationships = $profileCollection->relationships;

    expect(count($clientRelationships))->toEqual(1);
    expect(count($profileRelationships))->toEqual(1);

    expect($clientRelationships[0]['id'])->toBeUuid();
    // Client has one profile
    expect($clientRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($clientRelationships[0]['relationship_type'])->toEqual('hasOne');
    expect($clientRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($profileCollection->id));

    expect($profileRelationships[0]['id'])->toBeUuid();
    // Profile belongs to client
    expect($profileRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($profileCollection->id));
    expect($profileRelationships[0]['relationship_type'])->toEqual('belongsTo');
    expect($profileRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
});

// Update
it('one to one relationship update by adding a new relationship on a table that already has one thus json fields update accordingly and migrations run correctly too', function () {
    $profile = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Profile',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newProfile = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'NewProfile',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Client',
            'relationships' => [
                [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasOne',
                    'relationship_b_table' => TableHelper::uuidToTableName($profile->record->id)
                ],
            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Add a new one to one relationship then save
    $client = livewire(EditCollection::class, ['record' => $client->record->id])
        ->fillForm([
            'relationships' => [
                // Add a new one
                [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasOne',
                    'relationship_b_table' => TableHelper::uuidToTableName($newProfile->record->id)
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $clientCollection = Table::where('name', '=', 'Client')->first();
    $profileCollection = Table::where('name', '=', 'Profile')->first();
    $newProfileCollection = Table::where('name', '=', 'NewProfile')->first();
    /**
     * Check if the migrations ran correctly
     */

    $updatedClientTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $updatedProfileTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($profileCollection->id) . ')');
    $updatedNewProfileTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($newProfileCollection->id) . ')');

    expect(count($updatedClientTableSchema))->toEqual(1);
    expect(count($updatedProfileTableSchema))->toEqual(2);
    expect(count($updatedNewProfileTableSchema))->toEqual(2);
    
    expect($updatedClientTableSchema[0]->name)->toEqual('id');
    expect($updatedClientTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedClientTableSchema[0]->notnull)->toEqual(1);
    expect($updatedClientTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedClientTableSchema[0]->pk)->toEqual(1);

    expect($updatedProfileTableSchema[0]->name)->toEqual('id');
    expect($updatedProfileTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedProfileTableSchema[0]->notnull)->toEqual(1);
    expect($updatedProfileTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedProfileTableSchema[0]->pk)->toEqual(1);

    expect($updatedNewProfileTableSchema[0]->name)->toEqual('id');
    expect($updatedNewProfileTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedNewProfileTableSchema[0]->notnull)->toEqual(1);
    expect($updatedNewProfileTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedNewProfileTableSchema[0]->pk)->toEqual(1);

    expect($updatedProfileTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedProfileTableSchema[1]->type)->toEqual('INTEGER');
    expect($updatedProfileTableSchema[1]->notnull)->toEqual(1);
    expect($updatedProfileTableSchema[1]->dflt_value)->toBeNull();
    expect($updatedProfileTableSchema[1]->pk)->toEqual(0);

    expect($updatedNewProfileTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedNewProfileTableSchema[1]->type)->toEqual('INTEGER');
    expect($updatedNewProfileTableSchema[1]->notnull)->toEqual(1);
    expect($updatedNewProfileTableSchema[1]->dflt_value)->toBeNull();
    expect($updatedNewProfileTableSchema[1]->pk)->toEqual(0);

    $updatedClientTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $updatedProfileTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($profileCollection->id) . ')');
    $updatedNewProfileTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($newProfileCollection->id) . ')');

    expect(count($updatedClientTableForeignKeyList))->toEqual(0);
    expect(count($updatedProfileTableForeignKeyList))->toEqual(1);
    expect(count($updatedNewProfileTableForeignKeyList))->toEqual(1);

    expect($updatedProfileTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedProfileTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedProfileTableForeignKeyList[0]->to)->toEqual('id');
    expect($updatedProfileTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

    expect($updatedNewProfileTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedNewProfileTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedNewProfileTableForeignKeyList[0]->to)->toEqual('id');
    expect($updatedNewProfileTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

    /**
     * Check if the json relationships ran correctly
     */

    /**
     * Check if the correct data was deleted and if the data that should still be there is actually there
     */
    $clientRelationships = $clientCollection->relationships;
    $profileRelationships = $profileCollection->relationships;
    $newProfileRelationships = $newProfileCollection->relationships;

    expect(count($clientRelationships))->toEqual(2);
    expect(count($profileRelationships))->toEqual(1);
    expect(count($newProfileRelationships))->toEqual(1);

    expect($clientRelationships[0]['id'])->toBeUuid();
    // Client has one profile
    expect($clientRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($clientRelationships[0]['relationship_type'])->toEqual('hasOne');
    expect($clientRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($profileCollection->id));

    expect($clientRelationships[1]['id'])->toBeUuid();
    // Client has one profile
    expect($clientRelationships[1]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($clientRelationships[1]['relationship_type'])->toEqual('hasOne');
    expect($clientRelationships[1]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($newProfileCollection->id));
    
    expect($profileRelationships[0]['id'])->toBeUuid();

    // Profile belongs to client
    expect($profileRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($profileCollection->id));
    expect($profileRelationships[0]['relationship_type'])->toEqual('belongsTo');
    expect($profileRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));

    expect($newProfileRelationships[0]['id'])->toBeUuid();

    // New profile belongs to client
    expect($newProfileRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($newProfileCollection->id));
    expect($newProfileRelationships[0]['relationship_type'])->toEqual('belongsTo');
    expect($newProfileRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
});

// Delete
it('one to one relationship deleted thus json fields update accordingly and migrations run correctly too', function () {
    /**
     * We must check if the one to one relationship has both a "hasOne" as well as "belongsTo" relation.
     */
    $profile = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Profile',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $controlProfile = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'ControlProfile',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Client',
            'relationships' => [
                /**
                 * This will be used to test the delete functionality
                 */
                [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasOne',
                    'relationship_b_table' => TableHelper::uuidToTableName($profile->record->id)
                ],

                /**
                 * This will be used to check that only the right data was deleted (as a control)
                 */
                [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasOne',
                    'relationship_b_table' => TableHelper::uuidToTableName($controlProfile->record->id)
                ],
            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $clientCollection = Table::where('name', '=', 'Client')->first();
    $profileCollection = Table::where('name', '=', 'Profile')->first();
    $controlProfileCollection = Table::where('name', '=', 'ControlProfile')->first();

    // Navigate to the "Relationships" tab and delete the relationship then save
    $page = visit(TableResource::getUrl('edit', ['record' => $clientCollection]));
    $page->click('Relationships');
    $page->script("
        const btn = document.querySelector('button[title=\"Delete\"]');
        if (btn) {
            btn.click();
        }
    ");
    $page->click('Save changes');
    $page->wait(2);

    $updatedClientCollection = Table::where('name', '=', 'Client')->first();
    $updatedProfileCollection = Table::where('name', '=', 'Profile')->first();
    $updatedControlProfileCollection = Table::where('name', '=', 'ControlProfile')->first();

    /**
     * Check if the migrations ran correctly
     */

    $updatedClientTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($updatedClientCollection->id) . ')');
    $updatedProfileTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($updatedProfileCollection->id) . ')');
    $updatedControlProfileTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($updatedControlProfileCollection->id) . ')');

    expect(count($updatedClientTableSchema))->toEqual(1);
    expect(count($updatedProfileTableSchema))->toEqual(1);
    expect(count($updatedControlProfileTableSchema))->toEqual(2);
    
    expect($updatedClientTableSchema[0]->name)->toEqual('id');
    expect($updatedClientTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedClientTableSchema[0]->notnull)->toEqual(1);
    expect($updatedClientTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedClientTableSchema[0]->pk)->toEqual(1);

    expect($updatedProfileTableSchema[0]->name)->toEqual('id');
    expect($updatedProfileTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedProfileTableSchema[0]->notnull)->toEqual(1);
    expect($updatedProfileTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedProfileTableSchema[0]->pk)->toEqual(1);

    expect($updatedControlProfileTableSchema[0]->name)->toEqual('id');
    expect($updatedControlProfileTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedControlProfileTableSchema[0]->notnull)->toEqual(1);
    expect($updatedControlProfileTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedControlProfileTableSchema[0]->pk)->toEqual(1);

    expect($updatedControlProfileTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedControlProfileTableSchema[1]->type)->toEqual('INTEGER');
    expect($updatedControlProfileTableSchema[1]->notnull)->toEqual(1);
    expect($updatedControlProfileTableSchema[1]->dflt_value)->toBeNull();
    expect($updatedControlProfileTableSchema[1]->pk)->toEqual(0);
          
    $updatedClientTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($updatedClientCollection->id) . ')');
    $updatedProfileTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($updatedProfileCollection->id) . ')');
    $updatedControlProfileTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($updatedControlProfileCollection->id) . ')');

    expect(count($updatedClientTableForeignKeyList))->toEqual(0);
    expect(count($updatedProfileTableForeignKeyList))->toEqual(0);
    expect(count($updatedControlProfileTableForeignKeyList))->toEqual(1);

    expect($updatedControlProfileTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedControlProfileTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedControlProfileTableForeignKeyList[0]->to)->toEqual('id');
    expect($updatedControlProfileTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

    /**
     * Check if the json relationships ran correctly
     */

    /**
     * Check if the correct data was deleted and if the data that should still be there is actually there
     */
    expect(count($clientCollection->relationships))->toEqual(2);
    expect(count($updatedClientCollection->relationships))->toEqual(1);

    expect($updatedClientCollection->relationships[0]['id'])->toEqual($clientCollection->relationships[1]['id']);
    expect($updatedClientCollection->relationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedClientCollection->relationships[0]['relationship_type'])->toEqual($clientCollection->relationships[1]['relationship_type']);
    expect($updatedClientCollection->relationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($controlProfileCollection->id));

    expect(count($profileCollection->relationships))->toEqual(1);
    expect(count($updatedProfileCollection->relationships))->toEqual(0);

    expect(count($controlProfileCollection->relationships))->toEqual(1);
    expect(count($updatedControlProfileCollection->relationships))->toEqual(1);

    expect($updatedControlProfileCollection->relationships[0]['id'])->toEqual($controlProfileCollection->relationships[0]['id']);
    expect($updatedControlProfileCollection->relationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($controlProfileCollection->id));
    expect($updatedControlProfileCollection->relationships[0]['relationship_type'])->toEqual($controlProfileCollection->relationships[0]['relationship_type']);
    expect($updatedControlProfileCollection->relationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
});