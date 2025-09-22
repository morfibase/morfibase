<?php

namespace App\Filament\Resources\Tables\Schemas;

use App\Helpers\Table\FieldBlock;
use App\Helpers\Table\TableHelper;
use App\Models\Table;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = User::where('id', '=', Filament::auth()->user()->id)->first();

        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Fields')
                            ->schema([
                                Hidden::make('user_id')
                                    ->live()
                                    ->default(fn () => $user->id),

                                TextInput::make('name')
                                    ->live()
                                    ->required(),

                                Builder::make('display_field')
                                    ->live()
                                    ->label('Fields')
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->helperText('This field will be shown whenever this record is referenced in another table or dropdown.')
                                    ->collapsible(false)
                                    ->required()
                                    ->maxItems(1)
                                    ->blocks([
                                        FieldBlock::textInput(true)
                                            ->label('Display field'),
                                    ])
                                    ->blockNumbers(false)
                                    ->default([
                                        [
                                            'type' => 'textInput',
                                            'content' => '',
                                        ],
                                    ]),

                                Builder::make('fields')
                                    ->live()
                                    ->hiddenLabel()
                                    ->collapsible()
                                    ->addActionLabel('Add new field')
                                    ->blocks([
                                        FieldBlock::textInput(),
                                        FieldBlock::select(),
                                        FieldBlock::checkbox(),
                                        FieldBlock::toggle(),
                                        FieldBlock::checkboxList(),
                                        FieldBlock::radio(),
                                        FieldBlock::fileUpload(),
                                    ]),
                            ]),

                        Tab::make('Relationships')
                            ->schema([
                                Repeater::make('relationships')
                                    ->unique()
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->extraAttributes(['class' => 'flex flex-col at-relationship-schema-item'])
                                    ->schema([
                                        Hidden::make('id'),
                                        Hidden::make('relationship_a_table'),

                                        Text::make(function (Get $get) {
                                            $text = '';
                                            $collectionName = $get('../../name');
                                            $relationshipTypes = $get('relationship_type');

                                            if ($collectionName != null) {
                                                $text = 'Each '.lcfirst($collectionName);
                                            } else {
                                                $text = 'This table';
                                            }

                                            if (in_array($relationshipTypes, ['hasOne', 'hasMany'])) {
                                                $text .= ' has';
                                            }

                                            return $text;
                                        })
                                            ->extraAttributes(['class' => 'text-[.95rem] dark:text-white text-black']),

                                        Select::make('relationship_type')
                                            ->live()
                                            ->disabled(fn (Get $get) => $get('id') != null)
                                            // Without this, because this is disabled, it will never be sent in edit requests;
                                            ->dehydrated()
                                            ->required()
                                            ->hiddenLabel()
                                            ->native(false)
                                            ->options([
                                            'hasOne' => 'one',
                                            'hasMany' => 'many',
                                        ])
                                            ->extraAttributes(fn (Get $get): array => self::relationshipSelectStyle($get)),

                                        Select::make('relationship_b_table')
                                            ->disabled(fn (Get $get) => $get('id') != null)
                                            // Without this, because this is disabled, it will never be sent in edit requests;
                                            ->dehydrated()
                                            ->hiddenLabel()
                                            ->distinct()
                                            ->native(false)
                                            ->searchable()
                                            ->options(function (?Table $record) {
                                                $tables = Table::query();

                                                if ($record) {
                                                    $tables = $tables->where('id', '!=', $record->id);
                                                }

                                                $tables = $tables
                                                    ->get()
                                                    ->map(function ($item) {
                                                        $item->name = strtolower($item->name);
                                                        $item->id = TableHelper::uuidToTableName($item->id);

                                                        return $item;
                                                    })
                                                    ->pluck('name', 'id');

                                                return $tables;
                                            })
                                            ->extraAttributes(fn (Get $get): array => self::relationshipSelectStyle($get)),

                                        Text::make(function (Get $get) {
                                            $relationshipTypes = $get('relationship_type');

                                            if (in_array($relationshipTypes, ['hasOne'])) {
                                                return ' table.';
                                            } elseif ($relationshipTypes == 'hasMany') {
                                                return ' tables.';
                                            }
                                        })->extraAttributes(['class' => 'text-[.95rem] dark:text-white text-black']),
                                    ]),
                            ]),
                    ]),

            ]);
    }

    protected static function relationshipSelectStyle(Get $get): array
    {
        $hasValue = $get('relationship_type');
        $class = ['shadow-none'];

        if ($hasValue == null) {
            $class[] = 'italic';
        }

        return ['class' => implode(' ', $class)];
    }
}
