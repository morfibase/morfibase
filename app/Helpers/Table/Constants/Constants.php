<?php

namespace App\Helpers\Table\Constants;

class Constants
{
    public const INPUT_TYPES = [
        'email' => 'Email address',
        'numeric' => 'Numbers with decimals',
        'integer' => 'Whole numbers only',
        'password' => 'Hidden text',
        'tel' => 'Phone number',
        'url' => 'Website link',
    ];

    public const FORM_CLASS_REFERENCES = [
        'textInput' => 'Filament\Forms\Components\TextInput',
        'select' => 'Filament\Forms\Components\Select',
    ];

    public const TABLE_CLASS_REFERENCES = [
        // 'textInputColumn' => 'Filament\Tables\Columns\TextInputColumn',
        // 'selectColumn' => 'Filament\Tables\Columns\SelectColumn',

        'textColumn' => 'Filament\Tables\Columns\TextColumn',
    ];

    public const FORM_TO_TABLE_MAP = [
        // 'textInput' => 'textInputColumn',
        // 'select' => 'selectColumn',

        'textInput' => 'textColumn',
        'select' => 'textColumn',
    ];
}