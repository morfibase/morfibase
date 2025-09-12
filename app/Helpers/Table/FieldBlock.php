<?php

namespace App\Helpers\Table;

use App\Helpers\Table\Constants\Constants;
use App\Models\Table;
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

class FieldBlock
{
    public static function textInput(bool $isDisplayField = false): Block
    {
        return Block::make('textInput')
            ->label('Text Input')
            ->schema([
                Hidden::make('db_column_name'),

                TextInput::make('label')
                    ->required(),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('form.copyable'),

                        Fieldset::make('Validation')
                            ->columns(2)
                            ->schema([
                                Hidden::make('form.required')
                                    ->hidden($isDisplayField == false)
                                    ->default(true),

                                Toggle::make('form.required')
                                    ->hidden($isDisplayField == true)
                                    ->columnSpan(2),

                                Select::make('form.inputType')
                                    ->live()
                                    ->searchable()
                                    ->options(Constants::INPUT_TYPES)
                                    ->columnSpan(2),

                                Toggle::make('form.revealable')
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['password']) == false)
                                    ->columnSpan(2),

                                TextInput::make('form.step')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('form.minLength')
                                    ->live()
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('form.maxLength')
                                    ->numeric()
                                    ->minValue(fn(Get $get) => $get('minLength'))
                                    ->columnSpan(1),

                                TextInput::make('form.minValue')
                                    ->live()
                                    ->numeric()
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->columnSpan(1),

                                TextInput::make('form.maxValue')
                                    ->numeric()
                                    ->hidden(fn(Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->minValue(fn(Get $get) => $get('minValue'))
                                    ->columnSpan(1),
                            ]),

                        Fieldset::make('Default values')
                            ->columns(1)
                            ->schema([
                                TagsInput::make('form.datalist')
                                    ->placeholder('Write the new option and hit enter')
                                    ->helperText('Provide autocomplete options to users when they use the text input.')
                                    ->label('Autocomplete options'),
                            ]),

                        Fieldset::make('Input enhancements')
                            ->columns(2)
                            ->schema([
                                Toggle::make('form.autocapitalize')
                                    ->columnSpanFull()
                                    ->label('Autocapitalize words')
                                    ->helperText('Controls whether inputted text is automatically capitalized.'),

                                TextInput::make('form.prefix')
                                    ->columnSpan(1),

                                TextInput::make('form.suffix')
                                    ->columnSpan(1)
                            ]),
                    ]),

                Section::make('View settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('view.table.sortable'),
                        Toggle::make('view.table.searchable'),
                        Toggle::make('view.table.toggleable'),
                        TextInput::make('view.table.tooltip')
                    ])
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
                        TagsInput::make('form.options')
                            ->live()
                            ->placeholder('Write the new option and hit enter')
                            ->helperText('Provide options to users when they use the select.')
                            ->dehydrateStateUsing(fn($state) => collect($state)->mapWithKeys(fn($item) => [$item => $item])->toArray())
                            ->afterStateHydrated(function ($component, $state, ?Table $record) {
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
                                Toggle::make('form.required')
                                    ->columnSpan(2),

                                Toggle::make('form.multiple')
                                    ->live()
                                    ->columnSpan(2),

                                TextInput::make('form.minItems')
                                    ->hidden(fn (Get $get) => $get('multiple') == false)
                                    ->live()
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('form.maxItems')
                                    ->hidden(fn (Get $get) => $get('multiple') == false)
                                    ->numeric()
                                    ->minValue(fn(Get $get) => $get('minItems'))
                                    ->columnSpan(1),
                            ]),

                        Fieldset::make('Input enhancements')
                            ->columns(2)
                            ->schema([
                                TextInput::make('form.prefix')
                                    ->columnSpan(1),

                                TextInput::make('form.suffix')
                                    ->columnSpan(1)
                            ]),
                    ]),
            ]);
    }
}
