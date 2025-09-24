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
        'textColumn' => 'Filament\Tables\Columns\TextColumn',
        'imageColumn' => 'Filament\Tables\Columns\ImageColumn',
        'fileCountColumn' => 'App\Filament\Tables\Columns\FileCountColumn',
        'multiOptionColumn' => 'App\Filament\Tables\Columns\MultiOptionColumn',
    ];

    public const FORM_TO_TABLE_MAP = [
        'textInput' => 'textColumn',
        'select' => 'multiOptionColumn',
        'checkbox' => 'textColumn',
        'toggle' => 'textColumn',
        'checkboxList' => 'multiOptionColumn',
        'radio' => 'textColumn',
        'fileUpload' => 'fileCountColumn',
    ];
}
