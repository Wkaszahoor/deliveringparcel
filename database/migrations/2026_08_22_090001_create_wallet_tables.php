<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agent WL — wallet system (PM-014).
 *
 * ONE additive migration on the LIVE prod copy:
 *   wallets              — one balance row per user (locked on every mutation)
 *   wallet_transactions  — append-only ledger (credit/debit with balance_after)
 *   payments.order_id    — relaxed to NULL so wallet top-ups can reuse the
 *                          authoritative payments ledger + state machine
 *                          (MySQL allows multiple NULL rows inside the
 *                          unique(order_id, attempt) index, so order attempts
 *                          keep their idempotency guarantee untouched)
 *   payment_methods      — 'wallet' catalogue row + service-context rules,
 *                          plus a 'wallet_topup' context rule for Stripe
 *
 * No drops, no truncates, no destructive changes.
 */
class CreateWalletTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->decimal('balance', 12, 2)->default(0);   // major units, 2dp
                $table->string('currency', 3)->default('USD');
                $table->boolean('is_locked')->default(false);    // admin freeze switch
                $table->string('locked_reason', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->index();  // denormalized for direct queries
                $table->string('type', 30);                      // topup|order_payment|refund|adjustment
                $table->string('direction', 10);                 // credit|debit
                $table->decimal('amount', 12, 2);                // always positive
                $table->decimal('balance_after', 12, 2);         // running balance snapshot
                $table->unsignedBigInteger('payment_id')->nullable(); // payments.id linkage (topups/payments/refunds)
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('reference', 40)->nullable()->unique(); // idempotency key when set
                $table->string('description', 255)->nullable();
                $table->unsignedBigInteger('performed_by')->nullable(); // admin user id for adjustments
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['wallet_id', 'id']);
            });
        }

        // payments.order_id → nullable (lossless direction; existing rows untouched).
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'order_id')) {
            $nullable = DB::selectOne(
                "SELECT IS_NULLABLE AS n FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'order_id'"
            );
            if (($nullable->n ?? 'YES') === 'NO') {
                DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NULL');
            }
        }

        // Catalogue: wallet pay-from-balance method (only if the engine tables exist).
        if (Schema::hasTable('payment_methods') && Schema::hasTable('payment_method_rules')) {
            $methodId = DB::table('payment_methods')->where('code', 'wallet')->value('id');
            if (!$methodId) {
                $methodId = DB::table('payment_methods')->insertGetId([
                    'code'        => 'wallet',
                    'name'        => 'Wallet',
                    'gateway'     => 'wallet',
                    'description' => 'Pay instantly from your DeliveringParcel wallet balance.',
                    'is_enabled'  => true,
                    'priority'    => 5,
                    'min_amount'  => null,
                    'max_amount'  => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // Offer wallet for every service context that already allows Stripe
            // (mirrors the existing rails instead of inventing new rules).
            $stripeId = DB::table('payment_methods')->where('code', 'stripe')->value('id');
            $contexts = $stripeId
                ? DB::table('payment_method_rules')->where('payment_method_id', $stripeId)->pluck('service_context')->all()
                : [];
            foreach (array_unique(array_merge($contexts, ['ship_for_me', 'shop_for_me', 'custom'])) as $ctx) {
                $exists = DB::table('payment_method_rules')
                    ->where('service_context', $ctx)
                    ->where('payment_method_id', $methodId)
                    ->exists();
                if (!$exists) {
                    DB::table('payment_method_rules')->insert([
                        'service_context'   => $ctx,
                        'payment_method_id' => $methodId,
                        'is_allowed'        => true,
                        'priority'          => 5,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }

            // Top-up context: Stripe card only in v1 (bank/manual handled by admin adjustment).
            if ($stripeId) {
                $hasTopupRule = DB::table('payment_method_rules')
                    ->where('service_context', 'wallet_topup')
                    ->where('payment_method_id', $stripeId)
                    ->exists();
                if (!$hasTopupRule) {
                    DB::table('payment_method_rules')->insert([
                        'service_context'   => 'wallet_topup',
                        'payment_method_id' => $stripeId,
                        'is_allowed'        => true,
                        'priority'          => 1,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Additive-only policy on the prod copy: down() is intentionally a no-op
        // guard (tables are only dropped by an explicit, manual DBA action).
    }
}
