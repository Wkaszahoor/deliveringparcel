<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialsTable extends Migration
{
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('user_name');
            $t->string('user_avatar')->nullable(); // uploads/testimonials/...
            $t->string('role_or_company')->nullable();
            $t->text('content');
            $t->tinyInteger('rating')->nullable(); // 1-5
            $t->boolean('is_published')->default(false)->index();
            $t->integer('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonials');
    }
}
