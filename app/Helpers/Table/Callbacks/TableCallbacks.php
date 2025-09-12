<?php

namespace App\Helpers\Table\Callbacks;

use App\Models\GenericModel;
use Filament\Forms\Components\Field;

class TableCallbacks
{
    public static function textColumn(): array
    {
        return [

        ];
    }

    public static function textInputColumn(): array
    {
        return [
            'label' => fn(Field $input, array $params = []) => $input->label(...$params),
            
        ];
    }

    public static function selectColumn(): array
    {
        return [
            'label' => fn(Field $input, array $params = []) => $input->label(...$params),
        ];
    }
}