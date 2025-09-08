<?php

namespace App\Livewire;

use App\Exceptions\UserNotAuthenticatedException;
use App\Filament\Resources\Collections\CollectionResource;
use App\Helpers\StaticInstances\StaticUser;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Component;

class GenericCollectionSidebar extends Component
{
    public array | SupportCollection $collections = [];

    public function mount()
    {
        $this->collections = Collection::all()
            ->map(function(Collection $collection) {
                $collection->url = CollectionResource::getUrl('view', ['record' => $collection]);

                return $collection;
            });
    }
    public function render()
    {
        return view('livewire.generic-collection-sidebar');
    }
}
