<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperWalletTransactionsTable')) {
class CreateShipperWalletTransactionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_wallet_transactions')) {
            return;
        }
        Schema::create('shipper_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipper_profile_id');
            $table->enum('type', [
                'credit', 'debit', 'hold', 'hold_release',
                'payout_request', 'payout_paid', 'refund', 'bonus',
            ]);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2)->default(0);
            $table->unsignedInteger('order_id')->nullable();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->string('reference', 100)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->text('note')->nullable();
            $table->unsignedInteger('processed_by')->nullable();
            $table->timestamps();
            $table->index(['shipper_profile_id', 'type', 'created_at'], 'swt_profile_type_created_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_wallet_transactions');
    }
}
} // end class_exists guard
