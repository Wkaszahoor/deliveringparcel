<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payoneer link-payment flow (2026-09-12) — parallel to the payments engine.
 *
 * The request row tracks the MANUAL link handshake (client asks → admin
 * pastes a Payoneer "Request a Payment" link → client pays on Payoneer →
 * proof upload → admin verification). The money ledger itself lives in the
 * standard `payments` table: sendLink() creates a Payment row
 * (payment_method_code='payoneer') and verification reuses
 * PaymentService::verifyBankPayment / markBankPaymentReceived so the
 * order/offer sync path is EXACTLY the same as the bank flow.
 */
class CreatePayoneerRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('payoneer_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 32)->default('requested'); // requested|link_sent|proof_submitted|marked_paid|verified|rejected|cancelled
            $table->text('link_url')->nullable();
            $table->string('link_note', 1000)->nullable();
            $table->string('reject_reason', 1000)->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('link_sent_at')->nullable();
            $table->timestamp('proof_submitted_at')->nullable();
            $table->timestamp('marked_paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payoneer_requests');
    }
}
