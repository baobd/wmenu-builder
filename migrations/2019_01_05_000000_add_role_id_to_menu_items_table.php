<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleIdToMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = config('menu.table_prefix') . config('menu.table_name_items');

        // the column may already exist if an older copy of this migration was run
        if (Schema::hasColumn($table, 'role_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->integer('role_id')->default(0);
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

        if (!Schema::hasColumn($table, 'role_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
    }
}
