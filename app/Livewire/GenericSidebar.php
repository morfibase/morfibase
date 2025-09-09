<?php

namespace App\Livewire;

use App\Exceptions\UserNotAuthenticatedException;
use App\Filament\Resources\Tables\TableResource;
use App\Helpers\StaticInstances\StaticUser;
use App\Models\Organization;
use App\Models\Table;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;
use Livewire\Component;

class GenericSidebar extends Component
{
    public array | Collection $tables = [];

    public function mount()
    {
        $this->tables = Table::all()
            ->map(function(Table $table) {
                $table->url = TableResource::getUrl('view', ['record' => $table]);

                return $table;
            });
    }
    public function render()
    {
        return view('livewire.generic-sidebar');
    }
}
