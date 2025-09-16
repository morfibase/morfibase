<?php

namespace App\Livewire;

use App\Filament\Resources\Tables\TableResource;
use App\Models\Table;
use Illuminate\Support\Collection;
use Livewire\Component;

class GenericSidebar extends Component
{
    // Used to display system related sidebar items
    public array|Collection $system = [];

    public array|Collection $tables = [];

    public function mount()
    {
        $this->system = [
            [
                'name' => 'Tables',
                'url' => TableResource::getUrl('index'),
            ],
        ];

        $this->tables = Table::all()
            ->map(function (Table $table) {
                $table->url = TableResource::getUrl('view', ['record' => $table]);

                return $table;
            });
    }

    public function render()
    {
        return view('livewire.generic-sidebar');
    }
}
