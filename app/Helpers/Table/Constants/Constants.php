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
        'checkbox' => 'Filament\Forms\Components\Checkbox',
        'toggle' => 'Filament\Forms\Components\Toggle',
        'checkboxList' => 'Filament\Forms\Components\CheckboxList',
        'radio' => 'Filament\Forms\Components\Radio',
        'fileUpload' => 'Filament\Forms\Components\FileUpload',
    ];

    public const TABLE_CLASS_REFERENCES = [
        // 'textInputColumn' => 'Filament\Tables\Columns\TextInputColumn',
        // 'selectColumn' => 'Filament\Tables\Columns\SelectColumn',

        'textColumn' => 'Filament\Tables\Columns\TextColumn',
        'imageColumn' => 'Filament\Tables\Columns\ImageColumn',
    ];

    public const FORM_TO_TABLE_MAP = [
        // 'textInput' => 'textInputColumn',
        // 'select' => 'selectColumn',

        'textInput' => 'textColumn',
        'select' => 'textColumn',
        'checkbox' => 'textColumn',
        'toggle' => 'textColumn',
        'checkboxList' => 'textColumn',
        'radio' => 'textColumn',
        'fileUpload' => 'imageColumn',
    ];
}
