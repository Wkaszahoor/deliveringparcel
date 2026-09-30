<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperRatingsTable')) {
class CreateShipperRatingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_ratings')) {
            return;
        }
        Schema::create('shipper_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id')->unique();
            $table->unsignedInteger('order_id');
            $table->unsignedBigInteger('shipper_profile_id');
            $table->unsignedInteger('rated_by_user_id');
            $table->tinyInteger('overall_rating');
            $table->tinyInteger('communication_rating')->nullable();
            $table->tinyInteger('speed_rating')->nullable();
            $table->tinyInteger('value_rating')->nullable();
            $table->tinyInteger('condition_rating')->nullable();
            $table->text('review_text')->nullable();
            $table->enum('consent_testimonial', ['no', 'anonymous', 'first_name_only', 'full_name'])
                ->default('anonymous');
            $table->boolean('admin_approved')->default(false);
            $table->boolean('published_as_testimonial')->default(false);
            $table->timestamp('admin_approved_at')->nullable();
            $table->timestamps();
            $table->index(['shipper_profile_id', 'admin_approved']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_ratings');
    }
}
} // end class_exists guard
