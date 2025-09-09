<?php

use App\Filament\Resources\Tables\TableResource;
use App\Filament\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Tables\Pages\EditTable;
use App\Helpers\Table\TableHelper;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

// Create
it('one to many relationship saved corectly in the relationship json field as well as migrated correctly', function () {
    /**
     * We must check if the one to one relationship has both a "hasOne" as well as "belongsTo" relation.
     */
    $invoice = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Invoice',
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
                    'relationship_type' => 'hasMany',
                    'relationship_b_table' => TableHelper::uuidToTableName($invoice->record->id)
               ]
            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $clientCollection = Table::where('name', '=', 'Client')->first();
    $invoiceCollection = Table::where('name', '=', 'Invoice')->first();

    $clientTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $invoiceTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($invoiceCollection->id) . ')');

    /**
     * Check if the data was saved correctly from a migration point of view
     */
    expect(count($clientTableSchema))->toEqual(1);
    expect(count($invoiceTableSchema))->toEqual(2);

    expect($clientTableSchema[0]->name)->toEqual('id');
    expect($clientTableSchema[0]->type)->toEqual('INTEGER');
    expect($clientTableSchema[0]->notnull)->toEqual(1);
    expect($clientTableSchema[0]->dflt_value)->toBeNull();
    expect($clientTableSchema[0]->pk)->toEqual(1);

    expect($invoiceTableSchema[0]->name)->toEqual('id');
    expect($invoiceTableSchema[0]->type)->toEqual('INTEGER');
    expect($invoiceTableSchema[0]->notnull)->toEqual(1);
    expect($invoiceTableSchema[0]->dflt_value)->toBeNull();
    expect($invoiceTableSchema[0]->pk)->toEqual(1);

    expect($invoiceTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($invoiceTableSchema[1]->type)->toEqual('INTEGER');
    expect($invoiceTableSchema[1]->notnull)->toEqual(1);
    expect($invoiceTableSchema[1]->dflt_value)->toBeNull();
    expect($invoiceTableSchema[1]->pk)->toEqual(0);

    $clientTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $invoiceTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($invoiceCollection->id) . ')');

    expect(count($clientTableForeignKeyList))->toEqual(0);
    expect(count($invoiceTableForeignKeyList))->toEqual(1);
    
    expect($invoiceTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($invoiceTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($invoiceTableForeignKeyList[0]->to)->toEqual('id');
    expect($invoiceTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');



    expect($clientCollection)->not->toBeNull();
    expect($invoiceCollection)->not->toBeNull();

    $clientRelationships = $clientCollection->relationships;
    $invoiceRelationships = $invoiceCollection->relationships;

    expect(count($clientRelationships))->toEqual(1);
    expect(count($invoiceRelationships))->toEqual(1);

    expect($clientRelationships[0]['id'])->toBeUuid();
    // Client has many invoices
    expect($clientRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($clientRelationships[0]['relationship_type'])->toEqual('hasMany');
    expect($clientRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($invoiceCollection->id));

    expect($invoiceRelationships[0]['id'])->toBeUuid();
    // Profile belongs to client
    expect($invoiceRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($invoiceCollection->id));
    expect($invoiceRelationships[0]['relationship_type'])->toEqual('belongsTo');
    expect($invoiceRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
});

// Update
it('one to many relationship update by adding a new relationship on a table that already has one thus json fields update accordingly and migrations run correctly too', function () {
    $invoice = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Invoice',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newInvoice = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'NewInvoice',
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
                    'relationship_type' => 'hasMany',
                    'relationship_b_table' => TableHelper::uuidToTableName($invoice->record->id)
                ],
            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Add a new one to one relationship then save
    $client = livewire(EditTable::class, ['record' => $client->record->id])
        ->fillForm([
            'relationships' => [
                // Add a new one
                [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasMany',
                    'relationship_b_table' => TableHelper::uuidToTableName($newInvoice->record->id)
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $clientCollection = Table::where('name', '=', 'Client')->first();
    $invoiceCollection = Table::where('name', '=', 'Invoice')->first();
    $newInvoiceCollection = Table::where('name', '=', 'NewInvoice')->first();
    /**
     * Check if the migrations ran correctly
     */

    $updatedClientTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $updatedInvoiceTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($invoiceCollection->id) . ')');
    $updatedNewInvoiceTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($newInvoiceCollection->id) . ')');

    expect(count($updatedClientTableSchema))->toEqual(1);
    expect(count($updatedInvoiceTableSchema))->toEqual(2);
    expect(count($updatedNewInvoiceTableSchema))->toEqual(2);
    
    expect($updatedClientTableSchema[0]->name)->toEqual('id');
    expect($updatedClientTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedClientTableSchema[0]->notnull)->toEqual(1);
    expect($updatedClientTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedClientTableSchema[0]->pk)->toEqual(1);

    expect($updatedInvoiceTableSchema[0]->name)->toEqual('id');
    expect($updatedInvoiceTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedInvoiceTableSchema[0]->notnull)->toEqual(1);
    expect($updatedInvoiceTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedInvoiceTableSchema[0]->pk)->toEqual(1);

    expect($updatedNewInvoiceTableSchema[0]->name)->toEqual('id');
    expect($updatedNewInvoiceTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedNewInvoiceTableSchema[0]->notnull)->toEqual(1);
    expect($updatedNewInvoiceTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedNewInvoiceTableSchema[0]->pk)->toEqual(1);

    expect($updatedNewInvoiceTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedNewInvoiceTableSchema[1]->type)->toEqual('INTEGER');
    expect($updatedNewInvoiceTableSchema[1]->notnull)->toEqual(1);
    expect($updatedNewInvoiceTableSchema[1]->dflt_value)->toBeNull();
    expect($updatedNewInvoiceTableSchema[1]->pk)->toEqual(0);

    expect($updatedNewInvoiceTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedNewInvoiceTableSchema[1]->type)->toEqual('INTEGER');
    expect($updatedNewInvoiceTableSchema[1]->notnull)->toEqual(1);
    expect($updatedNewInvoiceTableSchema[1]->dflt_value)->toBeNull();
    expect($updatedNewInvoiceTableSchema[1]->pk)->toEqual(0);

    $updatedClientTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($clientCollection->id) . ')');
    $updatedInvoiceTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($invoiceCollection->id) . ')');
    $updatedNewInvoiceTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($newInvoiceCollection->id) . ')');

    expect(count($updatedClientTableForeignKeyList))->toEqual(0);
    expect(count($updatedInvoiceTableForeignKeyList))->toEqual(1);
    expect(count($updatedNewInvoiceTableForeignKeyList))->toEqual(1);

    expect($updatedInvoiceTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedInvoiceTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedInvoiceTableForeignKeyList[0]->to)->toEqual('id');
    expect($updatedInvoiceTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

    expect($updatedNewInvoiceTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedNewInvoiceTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedNewInvoiceTableForeignKeyList[0]->to)->toEqual('id');
    expect($updatedNewInvoiceTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

    /**
     * Check if the json relationships ran correctly
     */

    /**
     * Check if the correct data was deleted and if the data that should still be there is actually there
     */
    $clientRelationships = $clientCollection->relationships;
    $invoiceRelationships = $invoiceCollection->relationships;
    $newInvoiceRelationships = $newInvoiceCollection->relationships;

    expect(count($clientRelationships))->toEqual(2);
    expect(count($invoiceRelationships))->toEqual(1);
    expect(count($newInvoiceRelationships))->toEqual(1);

    expect($clientRelationships[0]['id'])->toBeUuid();
    // Client has one profile
    expect($clientRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($clientRelationships[0]['relationship_type'])->toEqual('hasMany');
    expect($clientRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($invoiceCollection->id));

    expect($clientRelationships[1]['id'])->toBeUuid();
    // Client has one profile
    expect($clientRelationships[1]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($clientRelationships[1]['relationship_type'])->toEqual('hasMany');
    expect($clientRelationships[1]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($newInvoiceCollection->id));
    
    expect($invoiceRelationships[0]['id'])->toBeUuid();

    // Profile belongs to client
    expect($invoiceRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($invoiceCollection->id));
    expect($invoiceRelationships[0]['relationship_type'])->toEqual('belongsTo');
    expect($invoiceRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));

    expect($newInvoiceRelationships[0]['id'])->toBeUuid();

    // New profile belongs to client
    expect($newInvoiceRelationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($newInvoiceCollection->id));
    expect($newInvoiceRelationships[0]['relationship_type'])->toEqual('belongsTo');
    expect($newInvoiceRelationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
});

// Delete
it('one to many relationship deleted thus json fields update accordingly and migrations run correctly too', function () {
    /**
     * We must check if the one to one relationship has both a "hasMany" as well as "belongsTo" relation.
     */
    $invoice = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'Invoice',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $controlInvoice = livewire(CreateTable::class)
        ->fillForm([
            'name' => 'ControlInvoice',
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
                    'relationship_b_table' => TableHelper::uuidToTableName($invoice->record->id)
                ],

                /**
                 * This will be used to check that only the right data was deleted (as a control)
                 */
                [
                    'id' => null,
                    'relationship_a_table' => null,
                    'relationship_type' => 'hasOne',
                    'relationship_b_table' => TableHelper::uuidToTableName($controlInvoice->record->id)
                ],
            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $clientCollection = Table::where('name', '=', 'Client')->first();
    $invoiceCollection = Table::where('name', '=', 'Invoice')->first();
    $controlInvoiceCollection = Table::where('name', '=', 'ControlInvoice')->first();

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
    $updatedInvoiceCollection = Table::where('name', '=', 'Invoice')->first();
    $updatedControlInvoiceCollection = Table::where('name', '=', 'ControlInvoice')->first();

    /**
     * Check if the migrations ran correctly
     */

    $updatedClientTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($updatedClientCollection->id) . ')');
    $updatedInvoiceTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($updatedInvoiceCollection->id) . ')');
    $updatedControlInvoiceTableSchema = DB::select('PRAGMA table_info(' . TableHelper::uuidToTableName($updatedControlInvoiceCollection->id) . ')');

    expect(count($updatedClientTableSchema))->toEqual(1);
    expect(count($updatedInvoiceTableSchema))->toEqual(1);
    expect(count($updatedControlInvoiceTableSchema))->toEqual(2);
    
    expect($updatedClientTableSchema[0]->name)->toEqual('id');
    expect($updatedClientTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedClientTableSchema[0]->notnull)->toEqual(1);
    expect($updatedClientTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedClientTableSchema[0]->pk)->toEqual(1);

    expect($updatedInvoiceTableSchema[0]->name)->toEqual('id');
    expect($updatedInvoiceTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedInvoiceTableSchema[0]->notnull)->toEqual(1);
    expect($updatedInvoiceTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedInvoiceTableSchema[0]->pk)->toEqual(1);

    expect($updatedControlInvoiceTableSchema[0]->name)->toEqual('id');
    expect($updatedControlInvoiceTableSchema[0]->type)->toEqual('INTEGER');
    expect($updatedControlInvoiceTableSchema[0]->notnull)->toEqual(1);
    expect($updatedControlInvoiceTableSchema[0]->dflt_value)->toBeNull();
    expect($updatedControlInvoiceTableSchema[0]->pk)->toEqual(1);

    expect($updatedControlInvoiceTableSchema[1]->name)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedControlInvoiceTableSchema[1]->type)->toEqual('INTEGER');
    expect($updatedControlInvoiceTableSchema[1]->notnull)->toEqual(1);
    expect($updatedControlInvoiceTableSchema[1]->dflt_value)->toBeNull();
    expect($updatedControlInvoiceTableSchema[1]->pk)->toEqual(0);
          
    $updatedClientTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($updatedClientCollection->id) . ')');
    $updatedInvoiceTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($updatedInvoiceCollection->id) . ')');
    $updatedControlInvoiceTableForeignKeyList = DB::select('PRAGMA foreign_key_list(' . TableHelper::uuidToTableName($updatedControlInvoiceCollection->id) . ')');

    expect(count($updatedClientTableForeignKeyList))->toEqual(0);
    expect(count($updatedInvoiceTableForeignKeyList))->toEqual(0);
    expect(count($updatedControlInvoiceTableForeignKeyList))->toEqual(1);

    expect($updatedControlInvoiceTableForeignKeyList[0]->table)->toEqual(TableHelper::uuidToTableName($clientCollection->id));
    expect($updatedControlInvoiceTableForeignKeyList[0]->from)->toEqual(TableHelper::uuidToForeignKeyName($clientCollection->id));
    expect($updatedControlInvoiceTableForeignKeyList[0]->to)->toEqual('id');
    expect($updatedControlInvoiceTableForeignKeyList[0]->on_delete)->toEqual('CASCADE');

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
    expect($updatedClientCollection->relationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($controlInvoiceCollection->id));

    expect(count($invoiceCollection->relationships))->toEqual(1);
    expect(count($updatedInvoiceCollection->relationships))->toEqual(0);

    expect(count($controlInvoiceCollection->relationships))->toEqual(1);
    expect(count($updatedControlInvoiceCollection->relationships))->toEqual(1);

    expect($updatedControlInvoiceCollection->relationships[0]['id'])->toEqual($controlInvoiceCollection->relationships[0]['id']);
    expect($updatedControlInvoiceCollection->relationships[0]['relationship_a_table'])->toEqual(TableHelper::uuidToTableName($controlInvoiceCollection->id));
    expect($updatedControlInvoiceCollection->relationships[0]['relationship_type'])->toEqual($controlInvoiceCollection->relationships[0]['relationship_type']);
    expect($updatedControlInvoiceCollection->relationships[0]['relationship_b_table'])->toEqual(TableHelper::uuidToTableName($clientCollection->id));
});