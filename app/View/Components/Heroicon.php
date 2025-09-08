<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Heroicon extends Component
{
    public function render()
    {
        // Use heroicons package or your SVG folder
        // For simplicity, assume SVGs are in resources/svg/heroicons/{name}.svg
        return view('components.heroicon');
    }
}
