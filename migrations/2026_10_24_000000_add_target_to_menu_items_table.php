<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTargetToMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = config('menu.table_prefix') . config('menu.table_name_items');

        if (Schema::hasColumn($table, 'target')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            // null = same tab, "_blank" = new tab
            $table->string('target', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = config('menu.table_prefix') . config('menu.table_name_items');

        if (!Schema::hasColumn($table, 'target')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('target');
        });
    }
}
