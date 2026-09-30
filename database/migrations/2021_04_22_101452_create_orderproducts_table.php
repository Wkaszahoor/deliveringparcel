<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderproducts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->text('productname');
            $table->text('producturl');
            $table->integer('productquantity');
            $table->text('productweight');
            $table->text('trackingid')->nullable();
            $table->text('productprice')->nullable();
            $table->text('product_total')->nullable();
            $table->text('trackinglink')->nullable();
            $table->text('image')->nullable();
            $table->text('receipt')->nullable();
            $table->text('custom_weight')->nullable();
            $table->text('custom_value')->nullable();
            $table->text('custom_category')->nullable();
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
        Schema::dropIfExists('orderproducts');
    }
}
