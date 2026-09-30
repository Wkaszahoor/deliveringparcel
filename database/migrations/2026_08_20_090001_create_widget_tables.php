<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetTables extends Migration
{
    public function up()
    {
        Schema::create('widget_instances', function (Blueprint $t) {
            $t->id();
            $t->string('type', 40);                      // key of config('admin_widgets.types')
            $t->string('title', 150);
            $t->string('area', 40);                      // key of config('admin_widgets.areas')
            $t->string('scope', 20)->default('global');  // page|template|theme|global (TW-004)
            $t->string('scope_key', 190)->nullable();    // url path (page) / template or theme name
            $t->json('config');                          // type-specific payload
            $t->json('conditions')->nullable();          // {auth:any|guest|user, roles:[]}
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(100);
            $t->unsignedInteger('cache_minutes')->nullable();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->timestamps();
            $t->index(['area', 'is_active', 'sort']);
            $t->index(['scope', 'scope_key']);
        });

        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id();
            $t->string('key', 120)->unique();
            $t->text('value')->nullable();
            $t->string('group', 40)->default('theme');
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('widget_instances');
        Schema::dropIfExists('theme_settings');
    }
}
