<?php

namespace App\Helpers\Table\Callbacks;

use Filament\Forms\Components\Field;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class TableCallbacks
{
    public static function textColumn(): array
    {
        return [
            'sortable' => fn (TextColumn $input, array $params = []) => $input->sortable(...$params),
            'searchable' => fn (TextColumn $input, array $params = []) => $input->searchable(...$params),
            'toggleable' => fn (TextColumn $input, array $params = []) => $input->toggleable(...$params),
        ];
    }

    public static function imageColumn(): array
    {
        return [
            'sortable' => fn (ImageColumn $input, array $params = []) => $input->sortable(...$params),
            'searchable' => fn (ImageColumn $input, array $params = []) => $input->searchable(...$params),
            'toggleable' => fn (ImageColumn $input, array $params = []) => $input->toggleable(...$params),
        ];
    }

    public static function textInputColumn(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
        ];
    }

    public static function selectColumn(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
        ];
    }
}
