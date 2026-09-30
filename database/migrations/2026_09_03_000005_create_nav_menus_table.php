<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

if (!class_exists('CreateNavMenusTable')) {
    class CreateNavMenusTable extends Migration
    {
        public function up()
        {
            Schema::create('nav_menus', function (Blueprint $table) {
                $table->id();
                $table->string('name');                     // "Header Main Menu"
                $table->string('location', 100)->unique();  // header_main | header_top | footer_col1..4
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('nav_menus')->insert([
                ['name' => 'Header Main Menu', 'location' => 'header_main',
                 'description' => 'Primary navigation in site header',
                 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Header Top Bar', 'location' => 'header_top',
                 'description' => 'Top bar links (login, track, etc.)',
                 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Footer Column 1', 'location' => 'footer_col1',
                 'description' => 'First footer column links',
                 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Footer Column 2', 'location' => 'footer_col2',
                 'description' => 'Second footer column links',
                 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Footer Column 3', 'location' => 'footer_col3',
                 'description' => 'Third footer column links',
                 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Footer Bottom', 'location' => 'footer_bottom',
                 'description' => 'Bottom footer legal links',
                 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        public function down()
        {
            Schema::dropIfExists('nav_menus');
        }
    }
} // end class_exists guard

return new CreateNavMenusTable();
