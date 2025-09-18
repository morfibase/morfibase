<?php

namespace App\Filament\Resources\Tables\Schemas;

use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class TableInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make('generic-table')
                    ->key('generic-table')
                    ->columnSpanFull(),
            ]);
    }
}
