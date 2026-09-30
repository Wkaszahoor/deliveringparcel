<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agent WL — PM-017: Stripe webhook history + COD gateway.
 *
 * Additive:
 *   webhook_events — EVERY received Stripe webhook event (processed, ignored,
 *                    signature-failed, errored) with payload + outcome, so the
 *                    admin can view full history and match against the Stripe
 *                    account.
 *   payment_methods/payment_method_rules — 'cod' (Cash on Delivery) gateway:
 *                    customer pays in cash at delivery, admin marks received.
 */
class CreateWebhookEventsAndCod extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('webhook_events')) {
            Schema::create('webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 110)->unique();   // evt_… (synthetic id for unsigned deliveries)
                $table->string('gateway', 30)->default('stripe');
                $table->string('type', 100)->nullable();     // payment_intent.succeeded | …
                $table->string('api_version', 30)->nullable();
                $table->string('status', 20)->default('received'); // received|processed|ignored|signature_failed|error
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->string('result', 255)->nullable();   // outcome text ("paid", "duplicate event ignored", …)
                $table->json('payload')->nullable();         // full event body (forensics/replay)
                $table->timestamp('received_at')->nullable();
                $table->timestamps();

                $table->index(['type', 'created_at']);
            });
        }

        // COD method + rules (mirrors the wallet seeding pattern).
        if (Schema::hasTable('payment_methods') && Schema::hasTable('payment_method_rules')) {
            $methodId = DB::table('payment_methods')->where('code', 'cod')->value('id');
            if (!$methodId) {
                $methodId = DB::table('payment_methods')->insertGetId([
                    'code'        => 'cod',
                    'name'        => 'Cash on Delivery',
                    'gateway'     => 'cod',
                    'description' => 'Pay in cash when your parcel is delivered. Our team confirms receipt after delivery.',
                    'is_enabled'  => true,
                    'priority'    => 60,
                    'min_amount'  => null,
                    'max_amount'  => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            $contexts = ['ship_for_me', 'shop_for_me', 'custom', 'shop'];
            foreach ($contexts as $ctx) {
                $exists = DB::table('payment_method_rules')
                    ->where('service_context', $ctx)
                    ->where('payment_method_id', $methodId)
                    ->exists();
                if (!$exists) {
                    DB::table('payment_method_rules')->insert([
                        'service_context'   => $ctx,
                        'payment_method_id' => $methodId,
                        'is_allowed'        => true,
                        'priority'          => 60,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Additive-only policy on the prod copy: no-op.
    }
}
