<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FE-004 / FE-005 — DB-driven header navigation.
 * Additive migration on the live copy.
 */
class CreateNavMenuItemsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nav_menu_items')) {
            return;
        }

        Schema::create('nav_menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('title', 100);          // admin-facing name
            $table->string('label', 100)->nullable(); // displayed text (falls back to title)
            $table->string('url', 500)->nullable();   // raw URL fallback (also external links)
            $table->string('route_name', 255)->nullable(); // preferred: named route
            $table->string('icon_class', 100)->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('new_tab')->default(false);
            $table->enum('visibility', ['everyone', 'guest', 'auth'])->default('everyone');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('nav_menu_items')->nullOnDelete();
            $table->index(['is_active', 'sort']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('nav_menu_items');
    }
}
