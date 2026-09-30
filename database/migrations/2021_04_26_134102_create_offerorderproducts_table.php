<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferorderproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offerorderproducts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->foreign('offer_id')->references('id')->on('offerorders')->onDelete('cascade');
            $table->text('productname');
            $table->text('producturl');
            $table->integer('productquantity');
            $table->integer('confirmation')->default('0');
            $table->text('trackingid')->nullable();
            $table->text('trackinglink')->nullable();
            $table->text('productspread')->nullable();
            $table->text('productprice')->nullable();
            $table->text('product_total')->nullable();
            $table->text('image')->nullable();
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
        Schema::dropIfExists('offerorderproducts');
    }
}
