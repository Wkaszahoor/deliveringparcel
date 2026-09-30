<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 100);
            $table->string('account_title', 100);
            $table->string('account_number', 50)->nullable();
            $table->string('iban', 100)->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('instructions')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index('is_enabled');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_accounts');
    }
}
