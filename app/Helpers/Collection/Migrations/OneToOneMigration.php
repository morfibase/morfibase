<?php

namespace App\Helpers\Collection\Migrations;

use App\Helpers\Collection\CollectionHelper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class OneToOneMigration
{
    /**
     * Run the up migration
     * 
     * @param string $tableA is the table which we are modifying
     * @param string $tableB is the table referenced in the foreign key (the fk points to this table)
     */
    public static function up(string $tableA, string $tableB)
    {
        Schema::table($tableA, function (Blueprint $table) use ($tableB) {
            $columnName = CollectionHelper::tableNameToForeignKeyName($tableB);
            $table->foreignId($columnName)->nullable()->unique();
            $table->foreign($columnName)
                ->references('id')
                ->on(DB::raw('`' . $tableB . '`'))
                ->onDelete('cascade');
        });
    }

    /**
     * Run the down migration
     * 
     * @param string $tableA is the table which we are modifying
     * @param string $tableB is the table referenced in the foreign key (the fk points to this table)
     */
    public static function down(string $tableA, string $tableB)
    {
        Schema::table($tableA, function (Blueprint $table) use ($tableB) {
            $columnName = CollectionHelper::tableNameToForeignKeyName($tableB);
            $table->dropForeign([$columnName]);
            $table->dropUnique([$columnName]);
            $table->dropColumn($columnName);
        });
    }
}