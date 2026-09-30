<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-archive for orders — admin cleanup of dead/test orders.
 * Purely additive: archived rows keep every column and relation intact;
 * list endpoints filter on archived_at, restore simply nulls it.
 */
class AddArchivedAtToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedInteger('archived_by')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'archived_by']);
        });
    }
}
