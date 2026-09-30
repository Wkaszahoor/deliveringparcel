<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * RV-002: reviews + review_filter_presets.
 *
 * Duplicate guard: MySQL UNIQUE indexes ignore NULL columns, so a plain
 * UNIQUE(user_id, order_id, ...) would NOT stop duplicates when
 * order_id/product_id/service_id are NULL. Instead the model computes a
 * deterministic `dedup_key` (all nullable FKs COALESCEd to 0) on insert and
 * a UNIQUE index on that column makes duplicates impossible at the DB level
 * (on top of the friendly server-side check in ReviewEligibility).
 *
 * rating is constrained 1-5 twice: app validation + a DB CHECK constraint.
 */
class CreateReviewsTables extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();

            // Reviewable targets (at least one is normally set).
            $table->unsignedBigInteger('order_id')->nullable()->index();      // → orders.id
            $table->unsignedBigInteger('order_item_id')->nullable()->index(); // → orderproducts.id
            $table->unsignedBigInteger('product_id')->nullable()->index();    // → shop_products.id
            $table->unsignedBigInteger('service_id')->nullable()->index();    // → services.id

            $table->string('review_type', 32)->default('order')->index(); // order|product|service|delivery|experience
            $table->tinyInteger('rating')->index();                       // 1-5 (CHECK below)

            $table->string('title', 120)->nullable();
            $table->text('body');

            $table->string('status', 32)->default('pending')->index(); // pending|approved|rejected|hidden|spam
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_verified_purchase')->default(false)->index();
            $table->boolean('is_published')->default(false)->index();  // only true after approval

            $table->text('admin_notes')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Deterministic duplicate guard (see docblock).
            $table->string('dedup_key', 120);
            $table->unique('dedup_key');

            $table->timestamps();

            // Secondary read indexes for the admin combined-filter queries.
            $table->index(['status', 'created_at']);
            $table->index(['is_verified_purchase', 'status']);
            $table->index(['review_type', 'status']);
        });

        // Hard 1-5 constraint at the database level (MySQL >= 8.0.16 enforces CHECK).
        DB::statement('ALTER TABLE `reviews` ADD CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)');

        Schema::create('review_filter_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique(); // e.g. homepage_reviews
            $table->json('filters');               // combined-filter payload (RV-005)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_filter_presets');
        Schema::dropIfExists('reviews');
    }
}
