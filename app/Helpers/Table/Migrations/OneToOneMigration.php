<?php

namespace App\Helpers\Table\Migrations;

use App\Helpers\Table\TableHelper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            $columnName = TableHelper::tableNameToForeignKeyName($tableB);
            $table->foreignUuid($columnName)->nullable()->unique();
            $table->foreign($columnName)
                ->references('id')
                ->on(DB::raw('`'.$tableB.'`'))
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
        Schema::table($tableA, function (Blueprint $table) use ($tableA, $tableB) {
            $columnName = TableHelper::tableNameToForeignKeyName($tableB);
            $foreignKeyName = MigrationHelper::generateForeignKeyName($tableA, $tableB, $columnName);

            $table->dropForeign([$columnName]);
            $table->dropUnique([$columnName]);
            $table->dropColumn($columnName);
        });
    }
}
