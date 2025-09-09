<?php

namespace App\Filament\Resources\Collections\Schemas;

use App\Helpers\Collection\CollectionHelper;
use App\Helpers\Collection\SchemaBlock;
use App\Models\Collection;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;

class CollectionForm
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
                        Tab::make('Schema')
                            ->schema([
                                Hidden::make('user_id')
                                    ->live()
                                    ->default(fn() => $user->id),

                                TextInput::make('name')
                                    ->live()
                                    ->required(),

                                Builder::make('display_field')
                                    ->label('Schema')
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->helperText('This field will be shown whenever this record is referenced in another table or dropdown.')
                                    ->collapsible(false)
                                    ->required()
                                    ->maxItems(1)
                                    ->blocks([
                                        SchemaBlock::textInput(true)
                                            ->label('Display field'),
                                    ])
                                    ->blockNumbers(false)
                                    ->default([
                                        [
                                            'type' => 'textInput',
                                            'content' => '',
                                        ],
                                    ]),

                                Builder::make('schema')
                                    ->hiddenLabel()
                                    ->collapsible()
                                    ->blocks([
                                        SchemaBlock::textInput(),
                                        SchemaBlock::select(),
                                    ])
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

                                        Text::make(function(Get $get) {
                                                $text = '';
                                                $collectionName = $get('../../name');
                                                $relationshipTypes = $get('relationship_type');
                                                
                                                if($collectionName != null) {
                                                    $text = 'Each ' . lcfirst($collectionName);
                                                } else {
                                                    $text = 'This collection';
                                                }

                                                if(in_array($relationshipTypes, ['hasOne', 'hasMany'])) {
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
                                            ->options(function (?Collection $record) {
                                                $collections = Collection::query();

                                                if ($record) {
                                                    $collections = $collections->where('id', '!=', $record->id);
                                                }

                                                $collections = $collections
                                                    ->get()
                                                    ->map(function($item) {
                                                        $item->name = strtolower($item->name);
                                                        $item->id = CollectionHelper::uuidToTableName($item->id);

                                                        return $item;
                                                    })
                                                    ->pluck('name', 'id');

                                                return $collections;
                                            })
                                            ->extraAttributes(fn (Get $get): array => self::relationshipSelectStyle($get)),

                                        Text::make(function (Get $get) {
                                            $relationshipTypes = $get('relationship_type');

                                            if(in_array($relationshipTypes, ['hasOne'])) {
                                                return ' collection.';
                                            } else if($relationshipTypes == 'hasMany') {
                                                return ' collections.';
                                            }
                                        })->extraAttributes(['class' => 'text-[.95rem] dark:text-white text-black']),
                                    ]),
                            ])
                    ])


            ]);
    }

    protected static function relationshipSelectStyle(Get $get): array
    {
        $hasValue = $get('relationship_type');
        $class = ['shadow-none'];

        if($hasValue == null) {
            $class[] = 'italic';
        }

        return ['class' => implode(' ', $class)];
    }
}
