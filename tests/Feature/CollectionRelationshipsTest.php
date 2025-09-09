<?php

use App\Filament\Resources\Tables\Pages\CreateTable;

use function Pest\Livewire\livewire;

it('can create a table with no realtionship', function () {
    livewire(CreateTable::class)
        ->fillForm([
            'name' => fake()->firstName(),
            'relationships' => [

            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});