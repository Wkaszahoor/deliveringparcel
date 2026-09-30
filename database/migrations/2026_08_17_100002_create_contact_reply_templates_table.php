<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent D — Contact/Messages upgrade.
 * New table for canned reply templates (guarded — never re-created / dropped).
 */
class CreateContactReplyTemplatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('contact_reply_templates')) {
            return;
        }

        Schema::create('contact_reply_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('subject', 191)->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down()
    {
        // Intentionally NOT dropping — live production copy.
    }
}
