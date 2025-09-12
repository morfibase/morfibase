<?php

namespace App\Filament\Resources\Tables;

use App\Filament\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Tables\Pages\EditTable;
use App\Filament\Resources\Tables\Pages\ListTables;
use App\Filament\Resources\Tables\Pages\ViewTable;
use App\Filament\Resources\Tables\Schemas\TableForm;
use App\Filament\Resources\Tables\Schemas\TableInfolist;
use App\Filament\Resources\Tables\Tables\TablesTable;
use App\Models\Table as TableModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::TableCells;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return TableForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TableInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTables::route('/'),
            'create' => CreateTable::route('/create'),
            'view' => ViewTable::route('/{record}'),
            'edit' => EditTable::route('/{record}/edit'),
        ];
    }
}
