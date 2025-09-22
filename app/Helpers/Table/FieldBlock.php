<?php

namespace App\Helpers\Table;

use App\Helpers\Table\Constants\Constants;
use App\Models\Table;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

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
                                    ->native(false)
                                    ->searchable()
                                    ->options(Constants::INPUT_TYPES)
                                    ->columnSpan(2),

                                Toggle::make('form.revealable')
                                    ->hidden(fn (Get $get) => in_array($get('inputType'), ['password']) == false)
                                    ->columnSpan(2),

                                TextInput::make('form.step')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->hidden(fn (Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('form.minLength')
                                    ->live()
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('form.maxLength')
                                    ->numeric()
                                    ->minValue(fn (Get $get) => $get('minLength'))
                                    ->columnSpan(1),

                                TextInput::make('form.minValue')
                                    ->live()
                                    ->numeric()
                                    ->hidden(fn (Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->columnSpan(1),

                                TextInput::make('form.maxValue')
                                    ->numeric()
                                    ->hidden(fn (Get $get) => in_array($get('inputType'), ['numeric', 'integer']) == false)
                                    ->minValue(fn (Get $get) => $get('minValue'))
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
                                    ->columnSpan(1),
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
                    ->compact()
                    ->schema([
                        TagsInput::make('form.options')
                            ->live()
                            ->placeholder('Write the new option and hit enter')
                            ->helperText('Provide options to users when they use the select.')
                            ->dehydrateStateUsing(fn ($state) => collect($state)->mapWithKeys(fn ($item) => [$item => $item])->toArray())
                            ->afterStateHydrated(function ($component, $state, ?Table $record) {
                                if (is_array($state) && $record) {
                                    $component->state(array_values($state)); // convert assoc back to plain array for the UI
                                }
                            }),
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
                                    ->minValue(fn (Get $get) => $get('minItems'))
                                    ->columnSpan(1),
                            ]),

                        Fieldset::make('Input enhancements')
                            ->columns(2)
                            ->schema([
                                TextInput::make('form.prefix')
                                    ->columnSpan(1),

                                TextInput::make('form.suffix')
                                    ->columnSpan(1),
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
                    ]),
            ]);
    }

    public static function checkbox(): Block
    {
        return Block::make('checkbox')
            ->label('Checkbox')
            ->schema([
                Hidden::make('db_column_name'),

                TextInput::make('label')
                    ->required(),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('form.inline')
                            ->helperText('When the checkbox is inline, its label is adjacent to it else its label is above it.')
                            ->default(true),

                        Fieldset::make('Validation')
                            ->schema([
                                Toggle::make('form.required')
                                    ->columnSpan(2),

                                Toggle::make('form.accepted')
                                    ->helperText('When checked the user must check the checkbox in order to submit the form.'),
                            ])
                    ]),

                Section::make('View settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('view.table.sortable'),
                        Toggle::make('view.table.searchable'),
                        Toggle::make('view.table.toggleable'),
                    ]),
            ]);
    }

    public static function toggle(): Block
    {
        return Block::make('toggle')
            ->label('Toggle')
            ->schema([
                Hidden::make('db_column_name'),

                TextInput::make('label')
                    ->required(),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('form.inline')
                            ->helperText('When the checkbox is inline, its label is adjacent to it else its label is above it.')
                            ->default(true),

                        Fieldset::make('Validation')
                            ->schema([
                                Toggle::make('form.required')
                                    ->columnSpan(2),

                                Toggle::make('form.accepted')
                                    ->helperText('When checked the user must check the checkbox in order to submit the form.'),
                            ])
                    ]),

                Section::make('View settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('view.table.sortable'),
                        Toggle::make('view.table.searchable'),
                        Toggle::make('view.table.toggleable'),
                    ]),
            ]);
    }

    public static function checkboxList(): Block
    {
        return Block::make('checkboxList')
            ->label('Checkbox list')
            ->schema([
                Hidden::make('db_column_name'),

                TextInput::make('label')
                    ->required(),

                Section::make('Data')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Toggle::make('form.searchable')
                            ->live(),

                        TextInput::make('form.noSearchResultsMessage')
                            ->required()
                            ->default('No results')
                            ->hidden(fn(Get $get) => $get('form.searchable') == false),

                        Toggle::make('form.bulkToggleable'),

                        TagsInput::make('form.options')
                            ->live()
                            ->placeholder('Write the new option and hit enter')
                            ->helperText('Provide options to users when they use the select.')
                            ->dehydrateStateUsing(fn ($state) => collect($state)->mapWithKeys(fn ($item) => [$item => $item])->toArray())
                            ->afterStateHydrated(function ($component, $state, ?Table $record) {
                                if (is_array($state) && $record) {
                                    $component->state(array_values($state)); // convert assoc back to plain array for the UI
                                }
                            }),
                    ]),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Fieldset::make('Validation')
                            ->schema([
                                Toggle::make('form.required')
                                    ->columnSpan(2),
                            ])
                    ]),

                Section::make('View settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('view.table.sortable'),
                        Toggle::make('view.table.searchable'),
                        Toggle::make('view.table.toggleable'),
                    ]),
            ]);
    }

    public static function radio(): Block
    {
        return Block::make('radio')
            ->label('Radio button')
            ->schema([
                Hidden::make('db_column_name'),

                TextInput::make('label')
                    ->required(),

                Section::make('Data')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        TagsInput::make('form.options')
                            ->live()
                            ->placeholder('Write the new option and hit enter')
                            ->helperText('Provide options to users when they use the select.')
                            ->dehydrateStateUsing(fn ($state) => collect($state)->mapWithKeys(fn ($item) => [$item => $item])->toArray())
                            ->afterStateHydrated(function ($component, $state, ?Table $record) {
                                if (is_array($state) && $record) {
                                    $component->state(array_values($state)); // convert assoc back to plain array for the UI
                                }
                            }),
                    ]),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Fieldset::make('Validation')
                            ->schema([
                                Toggle::make('form.required')
                                    ->columnSpan(2),

                                Toggle::make('form.inline')
                                    ->helperText('When the checkbox is inline, its label is adjacent to it else its label is above it.'),
                            ])
                    ]),

                Section::make('View settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('view.table.sortable'),
                        Toggle::make('view.table.searchable'),
                        Toggle::make('view.table.toggleable'),
                    ]),
            ]);
    }

    public static function fileUpload(): Block
    {
        return Block::make('fileUpload')
            ->label('File upload')
            ->schema([
                Hidden::make('db_column_name'),

                Hidden::make('form.directory')
                    ->default('uploads'),

                Hidden::make('form.multiple')
                    ->default(true),

                TextInput::make('label')
                    ->required(),

                Section::make('Form settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Select::make('form.disk')
                            ->native(false)
                            ->label('Visibility')
                            ->required()
                            ->options([
                                'local' => 'Private',
                                'public' => 'Public'
                            ]),

                        Fieldset::make('Validation')
                            ->schema([
                                Toggle::make('form.required')
                                    ->columnSpanFull()
                                    ->columnSpan(2),

                                Toggle::make('form.avatar')
                                    ->columnSpanFull(),

                                Toggle::make('form.image')
                                    ->columnSpanFull(),
                                
                                /**
                                 * When you save an edited image, it consideres that you have +1 image when you save so it will not work if 
                                 * the user is already at maxFiles.
                                 * That is why we disabled the image editor an all its features
                                 * This issue was documented here: https://github.com/filamentphp/filament/issues/14307
                                 */
                                // Toggle::make('form.imageEditor')
                                //     ->columnSpanFull()
                                //     ->live(),

                                // Fieldset::make('Image editor settings')
                                //     ->columnSpanFull()
                                //     ->hidden(fn (Get $get) => $get('form.imageEditor') == false)
                                //     ->schema([
                                //         Select::make('form.imageEditorAspectRatios')
                                //             ->columnSpanFull()
                                //             ->multiple()
                                //             ->options([
                                //                 null => 'No aspect ratio',
                                //                 '16:9' => '16:9',
                                //                 '4:3' => '4:3',
                                //                 '1:1' => '1:1',
                                //             ]),

                                //         ColorPicker::make('form.imageEditorEmptyFillColor')
                                //             ->columnSpanFull(),

                                //         TextInput::make('form.imageEditorViewportWidth')
                                //             ->columnSpanFull(),

                                //         TextInput::make('form.imageEditorViewportHeight')
                                //             ->columnSpanFull(),

                                //         Toggle::make('form.circleCropper')
                                //             ->columnSpanFull(),

                                //         Select::make('form.imageResizeMode')
                                //             ->columnSpanFull()
                                //             ->options([
                                //                 'force' => 'Force',
                                //                 'cover' => 'Cover',
                                //                 'contain' => 'Contain',
                                //             ]),
                                //     ]),

                                Toggle::make('form.reorderable')
                                    ->columnSpanFull(),

                                Toggle::make('form.openable')
                                    ->columnSpanFull(),

                                Toggle::make('form.downloadable')
                                    ->columnSpanFull(),

                                Toggle::make('form.previewable')
                                    ->columnSpanFull()
                                    ->default(true),

                                Toggle::make('form.deletable')
                                    ->columnSpanFull()
                                    ->default(true),

                                Select::make('form.acceptedFileTypes')
                                    ->multiple()
                                    ->columnSpanFull()
                                    ->options([
                                        'application/pdf' => 'application/pdf',
                                        'application/msword' => 'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel' => 'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-powerpoint' => 'application/vnd.ms-powerpoint',
                                        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                        'text/plain' => 'text/plain',
                                        'text/csv' => 'text/csv',
                                        'image/jpeg' => 'image/jpeg',
                                        'image/png' => 'image/png',
                                        'image/gif' => 'image/gif',
                                        'image/webp' => 'image/webp',
                                        'audio/mpeg' => 'audio/mpeg',
                                        'audio/wav' => 'audio/wav',
                                        'video/mp4' => 'video/mp4',
                                        'video/x-msvideo' => 'video/x-msvideo',
                                        'video/quicktime' => 'video/quicktime',
                                        'application/zip' => 'application/zip',
                                        'application/x-rar-compressed' => 'application/x-rar-compressed',
                                    ]),

                                TextInput::make('form.minFiles')
                                    ->live()
                                    ->required()
                                    ->label('Minimum number of files')
                                    ->columnSpanFull()
                                    ->minValue(0)
                                    ->default(0),

                                TextInput::make('form.maxFiles')
                                    ->required()
                                    ->columnSpanFull()
                                    ->label('Maximum number of files')
                                    ->minValue(fn(Get $get) => $get('form.minFiles'))
                                    ->default(1),

                                TextInput::make('form.minSize')
                                    ->live()
                                    ->label('Minimum size of a file')
                                    ->columnSpanFull()
                                    ->minValue(0),

                                TextInput::make('form.maxSize')
                                    ->columnSpanFull()
                                    ->label('Maximum size of a file')
                                    ->minValue(fn(Get $get) => $get('form.minSize')),   
                            ])
                    ]),

                Section::make('View settings')
                    ->collapsible()
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Toggle::make('view.table.sortable'),
                        Toggle::make('view.table.searchable'),
                        Toggle::make('view.table.toggleable'),
                    ]),
            ]);
    }
}
