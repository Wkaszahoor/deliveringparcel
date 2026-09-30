<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent PM — Dynamic payment engine (PM-001/PM-006/PM-012).
 *
 * ONE additive migration creating three tables on the LIVE prod copy:
 *   payment_methods       — configurable catalogue (code/gateway/priority/limits)
 *   payment_method_rules  — service-context → method mapping (admin editable)
 *   payments              — authoritative ledger + state machine rows
 *
 * No drops, no truncates, no changes to existing tables.
 */
class CreatePaymentTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();          // stripe | bank_transfer | future adapters
                $table->string('name', 100);                   // customer-facing label
                $table->string('gateway', 50);                 // adapter key (StripeGateway::code())
                $table->string('description', 255)->nullable();// selector hint
                $table->boolean('is_enabled')->default(true);  // disabled methods are never offered
                $table->unsignedInteger('priority')->default(100); // lower = offered first
                $table->decimal('min_amount', 12, 2)->nullable();
                $table->decimal('max_amount', 12, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_method_rules')) {
            Schema::create('payment_method_rules', function (Blueprint $table) {
                $table->id();
                $table->string('service_context', 30);         // ship_for_me | shop_for_me | custom
                $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
                $table->boolean('is_allowed')->default(true);
                $table->unsignedInteger('priority')->nullable(); // optional per-context order override
                $table->timestamps();

                $table->unique(['service_context', 'payment_method_id'], 'pmr_context_method_unique');
                $table->index('service_context');
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 40)->unique();      // DP-<orderid>-<attempt>-<rand> (PM-012)
                $table->unsignedBigInteger('order_id')->index();// orders.id
                $table->string('request_id', 50)->nullable()->index(); // optional custom-request linkage (PM-006)
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedInteger('attempt')->default(1);

                // Amounts are ALWAYS resolved server-side from the accepted
                // offerorder (PM-009). Major units (e.g. USD dollars), 2dp.
                $table->decimal('amount', 12, 2);
                $table->decimal('amount_refunded', 12, 2)->default(0);
                $table->string('currency', 3)->default('USD');

                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
                $table->string('payment_method_code', 30)->nullable(); // snapshot, survives method deletion
                $table->string('gateway', 50)->nullable();             // adapter snapshot

                $table->string('gateway_transaction_id', 100)->nullable()->index();
                $table->string('status', 30)->default('pending')->index();
                $table->string('status_set_by', 20)->default('system'); // customer|admin|webhook|system

                $table->timestamp('initiated_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable(); // admin user id (bank verify)

                $table->string('proof_path', 255)->nullable();   // bank transfer receipt (PM-008)
                $table->timestamp('proof_uploaded_at')->nullable();
                $table->string('failure_reason', 255)->nullable(); // internal/admin only, never echoed raw to customers

                $table->json('metadata')->nullable();            // gateway payloads, webhook event ids, audit extras
                $table->timestamps();

                // Idempotency (PM-012): one row per order attempt + unique reference.
                $table->unique(['order_id', 'attempt'], 'payments_order_attempt_unique');
                $table->index('status');
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down()
    {
        // Additive-only policy on the prod copy: down() is intentionally a no-op
        // guard (tables are only dropped by an explicit, manual DBA action).
    }
}
