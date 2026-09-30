<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnsAndClaimsTables extends Migration
{
    public function up()
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('reason', 190)->index();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('requested')->index(); // requested|approved|received|refunded|rejected
            $table->text('resolution_note')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_request_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('type', 32)->default('damage')->index(); // damage|loss|late
            $table->decimal('amount_claimed', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('evidence')->nullable();                      // file paths
            $table->string('status', 32)->default('open')->index();     // open|investigation|approved|denied|settled
            $table->decimal('payout', 10, 2)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('claims');
        Schema::dropIfExists('return_requests');
    }
}
