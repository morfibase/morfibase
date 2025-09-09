<?php

namespace App\Helpers\Collection;

use App\Helpers\Collection\Constants\Constants;
use App\Models\Collection;
use App\Models\GenericModel;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Components\Text;

class SchemaBlock
{
    public static function sharedTable()
    {
        return [
            Toggle::make('sortable'),
            Toggle::make('searchable'),
            Toggle::make('toggleable'),
            TextInput::make('tooltip')
        ];
    }

    public static function textInput(bool $isDisplayField = false): Block
    {
        return Block::make('textInput')
            ->label('Text Input')
            ->schema([
                Hidden::make('db_column_name'),

                Section::make('General settings')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        TextInput::make('label')
                            ->required(),

                        Toggle::make('copyable'),
                    ]),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Fieldset::make('Validation')
                            ->columns(2)
                            ->schema([
                                Toggle::make('required')
                                    ->disabled($isDisplayField == true)
                                    ->default(true)
                                    ->helperText(fn() => $isDisplayField ? 'This field serves as the Display Field and cannot be empty.' : '')
                                    ->columnSpan(2),

                                Select::make('inputType')
                                    ->live()
                                    ->searchable()
                                    ->options(Constants::INPUT_TYPES)
                                    ->columnSpan(2),

                                Toggle::make('revealable')
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['password']) == false)
                                    ->columnSpan(2),

                                TextInput::make('step')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('minLength')
                                    ->live()
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('maxLength')
                                    ->numeric()
                                    ->minValue(fn(Get $get) => $get('minLength'))
                                    ->columnSpan(1),

                                TextInput::make('minValue')
                                    ->live()
                                    ->numeric()
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->columnSpan(1),

                                TextInput::make('maxValue')
                                    ->numeric()
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->minValue(fn(Get $get) => $get('minValue'))
                                    ->columnSpan(1),
                            ]),

                        Fieldset::make('Default values')
                            ->columns(1)
                            ->schema([
                                TagsInput::make('datalist')
                                    ->placeholder('Write the new option and hit enter')
                                    ->helperText('Provide autocomplete options to users when they use the text input.')
                                    ->label('Autocomplete options'),
                            ]),

                        Fieldset::make('Input enhancements')
                            ->columns(2)
                            ->schema([
                                Toggle::make('autocapitalize')
                                    ->columnSpanFull()
                                    ->label('Autocapitalize words')
                                    ->helperText('Controls whether inputted text is automatically capitalized.'),

                                TextInput::make('prefix')
                                    ->columnSpan(1),

                                TextInput::make('suffix')
                                    ->columnSpan(1)
                            ]),
                    ]),

                Section::make('Table settings')
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->schema([
                        ...self::sharedTable()
                    ]),
            ]);
    }

    public static function select(): Block
    {
        return Block::make('select')
            ->label('Select')
            ->schema([
                Hidden::make('db_column_name'),
                Hidden::make('native')->default(false),

                Section::make('General settings')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        TextInput::make('label')
                            ->required(),
                    ]),

                Section::make('Data')
                    ->collapsible()
                    // ->collapsed()
                    ->compact()
                    ->schema([    
                        TagsInput::make('options')
                            ->live()
                            ->placeholder('Write the new option and hit enter')
                            ->helperText('Provide options to users when they use the select.')
                            ->dehydrateStateUsing(fn($state) => collect($state)->mapWithKeys(fn($item) => [$item => $item])->toArray())
                            ->afterStateHydrated(function ($component, $state, ?Collection $record) {
                                if (is_array($state) && $record) {
                                    $component->state(array_values($state)); // convert assoc back to plain array for the UI
                                }
                            })
                    ]),

                Section::make('Form settings')
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->schema([
                        Fieldset::make('Validation')
                            ->columns(2)
                            ->schema([
                                Toggle::make('required')
                                    ->columnSpan(2),

                                Toggle::make('multiple')
                                    ->live()
                                    ->columnSpan(2),

                                TextInput::make('minItems')
                                    ->hidden(fn (Get $get) => $get('multiple') == false)
                                    ->live()
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('maxItems')
                                    ->hidden(fn (Get $get) => $get('multiple') == false)
                                    ->numeric()
                                    ->minValue(fn(Get $get) => $get('minItems'))
                                    ->columnSpan(1),
                            ]),

                        Fieldset::make('Input enhancements')
                            ->columns(2)
                            ->schema([
                                TextInput::make('prefix')
                                    ->columnSpan(1),

                                TextInput::make('suffix')
                                    ->columnSpan(1)
                            ]),
                    ]),
            ]);
    }
}
