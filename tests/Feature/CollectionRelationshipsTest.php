<?php

use App\Filament\Resources\Tables\Pages\CreateTable;
use Filament\Forms\Components\Builder;

use function Pest\Livewire\livewire;

it('can create a table with no realtionship', function () {
    $undoBuilderFake = Builder::fake();

    livewire(CreateTable::class)
        ->fillForm([
            'name' => fake()->firstName(),
            'display_field' => [
                [
                    'data' => [
                        'label' => 'DisplayFieldName',
                    ],
                    'type' => 'textInput',
                ],
            ],
            'relationships' => [

            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undoBuilderFake();
});
