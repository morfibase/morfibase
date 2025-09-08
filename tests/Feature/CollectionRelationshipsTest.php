<?php

use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Helpers\Collection\CollectionHelper;
use App\Models\Collection;

use function Pest\Livewire\livewire;

it('can create a collection with no realtionship', function () {
    livewire(CreateCollection::class)
        ->fillForm([
            'name' => fake()->firstName(),
            'relationships' => [

            ]
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});