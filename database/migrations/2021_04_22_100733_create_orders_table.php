<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_id');
            $table->string('shipfrom');
            $table->string('shipto');
            $table->string('postalcode');
            $table->text('address');
            $table->string('approximate_weight');
            $table->integer('product_photo')->default('0');
            $table->integer('product_customs')->default('0');
            $table->integer('product_check')->default('0');
            $table->integer('product_prohibited')->default('0');
            $table->integer('product_disinfection')->default('0');
            $table->integer('product_consolidation')->default('0');
            $table->integer('product_services')->default('0');
            $table->integer('product_purchase')->default('0');
            $table->integer('product_totalprice')->default('0');
            $table->text('edit_offer')->nullable();
            $table->string('total')->default('0');
            $table->text('order_status')->nullable();
            $table->integer('active_tab')->default('1');
            $table->integer('confirmation')->default('0');
            $table->integer('tracking_status')->default('0');
            $table->text('trackingid')->nullable();
            $table->text('trackinglink')->nullable();
            $table->text('companyname')->nullable();
            $table->text('custom_status')->nullable();
            $table->text('ship_name')->nullable();
            $table->text('ship_address1')->nullable();
            $table->text('ship_address2')->nullable();
            $table->text('ship_city')->nullable();
            $table->text('ship_state')->nullable();
            $table->text('ship_postalcode')->nullable();
            $table->text('ship_country')->nullable();
            $table->text('ship_number')->nullable();
            $table->text('custom_category')->nullable();
            $table->text('custom_total_quantity')->nullable();
            $table->text('custom_total_weight')->nullable();
            $table->text('custom_total_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
