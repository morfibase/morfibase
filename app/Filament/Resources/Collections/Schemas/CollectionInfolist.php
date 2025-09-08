<?php

namespace App\Filament\Resources\Collections\Schemas;

use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class CollectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make('generic-collection-table')
                    ->key('generic-collection-table')
                    ->columnSpanFull()
            ]);
    }
}
