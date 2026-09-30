<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent D — Contact/Messages upgrade.
 *
 * SAFE migration on a LIVE production copy:
 * - only ADDs nullable columns
 * - every column is guarded with Schema::hasColumn()
 * - never drops / modifies existing columns
 */
class AddClassificationColumnsToContactusesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('contactuses')) {
            return;
        }

        Schema::table('contactuses', function (Blueprint $table) {
            if (!Schema::hasColumn('contactuses', 'category')) {
                $table->string('category', 32)->nullable()->default('general')->index();
            }
            if (!Schema::hasColumn('contactuses', 'status')) {
                $table->string('status', 16)->nullable()->default('new')->index();
            }
            if (!Schema::hasColumn('contactuses', 'reply_body')) {
                $table->text('reply_body')->nullable();
            }
            if (!Schema::hasColumn('contactuses', 'replied_by')) {
                $table->unsignedBigInteger('replied_by')->nullable();
            }
            if (!Schema::hasColumn('contactuses', 'replied_at')) {
                $table->timestamp('replied_at')->nullable();
            }
            if (!Schema::hasColumn('contactuses', 'classified_at')) {
                $table->timestamp('classified_at')->nullable();
            }
        });
    }

    public function down()
    {
        // Intentionally NOT dropping columns — live production copy.
    }
}
