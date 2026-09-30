<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopTables extends Migration
{
    public function up()
    {
        Schema::create('shop_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('shop_products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('sku')->nullable()->index();
            $t->unsignedBigInteger('shop_category_id')->nullable()->index();
            $t->text('description')->nullable();
            $t->decimal('price', 10, 2)->default(0);
            $t->decimal('compare_price', 10, 2)->nullable();
            $t->integer('stock')->default(0);
            $t->json('images')->nullable();
            $t->boolean('is_active')->default(true)->index();
            $t->boolean('featured')->default(false)->index();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('shop_reviews', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('shop_product_id')->index();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->tinyInteger('rating')->default(5);
            $t->string('title')->nullable();
            $t->text('body')->nullable();
            $t->boolean('is_approved')->default(false)->index();
            $t->timestamps();
        });

        Schema::create('shop_coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->enum('type', ['percent', 'fixed'])->default('percent');
            $t->decimal('value', 10, 2)->default(0);
            $t->decimal('min_order', 10, 2)->nullable();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('shop_orders', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30)->unique();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->decimal('total', 10, 2)->default(0);
            $t->string('status', 32)->default('pending')->index();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });

        Schema::create('shop_order_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('shop_order_id')->index();
            $t->unsignedBigInteger('shop_product_id')->nullable()->index();
            $t->string('name');
            $t->integer('qty')->default(1);
            $t->decimal('price', 10, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_orders');
        Schema::dropIfExists('shop_coupons');
        Schema::dropIfExists('shop_reviews');
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('shop_categories');
    }
}
