<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarded, additive-only migration (LIVE prod copy — never drop/truncate).
 *
 * 1. orders.archived_at  — nullable timestamp used by Admin OrdersMgmt archive feature.
 * 2. users.status        — nullable varchar(20); NULL is treated as "active"
 *                          so every existing row keeps behaving exactly as before.
 *
 * Both adds are wrapped in Schema::hasColumn() guards so the migration is
 * safe to re-run and is a no-op when the column already exists in production.
 */
class AddArchivedAtToOrdersAndStatusToUsers extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('orders', 'archived_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            });
        }

        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 20)->nullable()->after('avatar');
            });
        }
    }

    public function down()
    {
        // Intentionally empty: this is a live production copy — never drop columns.
    }
}
