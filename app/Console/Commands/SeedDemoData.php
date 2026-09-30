<?php

namespace App\Console\Commands;

use App\Models\Offerorder;
use App\Models\Orderproducts;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * [TEST] dp:seed-demo — insert clearly-marked DEMO data for testing the
 * order/payment flows (ship-for-me, custom/heavy cargo, book-for-me).
 *
 * Everything created here is identifiable:
 *   - users:  name prefixed "[TEST] ", email on @dptest.local
 *   - offers/descriptions: contain "[TEST]" + "DPTEST"
 *   - payments: references prefixed "DPTEST-"
 *
 * IDEMPOTENT: the command first deletes ONLY rows it previously created
 * (identified by the markers above), then re-inserts them. It never touches
 * real production rows. Rows are written directly (no PaymentService state
 * machine) but kept valid for the payments engine schema.
 */
class SeedDemoData extends Command
{
    protected $signature = 'dp:seed-demo';

    protected $description = '[TEST] Seed clearly-marked demo data (2 client users, 3 orders, offers, payments, admin notification) for order/payment flow testing. Idempotent.';

    /** Email domain marker identifying every demo row this command owns. */
    private const EMAIL_MARKER = '@dptest.local';

    /** Marker embedded in demo payloads (offers, notification data). */
    private const PAYLOAD_MARKER = 'DPTEST';

    private const DEMO_PASSWORD = 'Test@1234';

    public function handle()
    {
        $this->info('[TEST] dp:seed-demo — inserting clearly-marked demo data');

        DB::transaction(function () {
            $removed = $this->cleanupPreviousRun();
            $methods = $this->ensurePaymentMethods();

            $clientRole = Role::where('slug', 'client')->orderBy('id')->firstOrFail();

            $alice = $this->createDemoUser('[TEST] Alice Tester', 'alice@dptest.local', '+4930123456789', $clientRole);
            $bob   = $this->createDemoUser('[TEST] Bob Booker', 'bob@dptest.local', '+12125551234', $clientRole);

            $nextRef = $this->nextOrderRef();

            // ---- Order A1 (Alice, ship-for-me): pending offer ----
            $a1Ref = $nextRef++;
            $a1 = Orders::create([
                'user_id'            => $alice->id,
                'order_id'           => (string) $a1Ref,
                'shipfrom'           => 'Berlin, Germany',
                'shipto'             => 'Lahore, Pakistan',
                'postalcode'         => '54000',
                'address'            => '[TEST] Demo Street 1, Lahore, Pakistan',
                'approximate_weight' => '5',
                'product_services'   => 1,
                'product_purchase'   => 0,
                'total'              => '85',
                'order_status'       => 'pending',
                'confirmation'       => 0,
            ]);

            Orderproducts::create([
                'order_id'       => $a1->id,
                'productname'    => '[TEST] Demo Item 1',
                'producturl'     => 'https://example.com/item-1',
                'productweight'  => '2',
                'productquantity' => 2,
                'productprice'   => '25.00',
                'product_total'  => '50.00',
            ]);
            Orderproducts::create([
                'order_id'       => $a1->id,
                'productname'    => '[TEST] Demo Item 2',
                'producturl'     => 'https://example.com/item-2',
                'productweight'  => '3',
                'productquantity' => 1,
                'productprice'   => '30.00',
                'product_total'  => '30.00',
            ]);

            Offerorder::create([
                'order_id'        => $a1->id,
                'shipingaddress'  => '[TEST] Lahore, Pakistan',
                'description'     => '[TEST] DPTEST demo offer for ship-for-me order (pending review).',
                'total'           => 85,
                'product_total'   => 85,
                'offer_status'    => 0, // pending
            ]);

            // ---- Order A2 (Alice, custom / heavy cargo flow): accepted offer + bank transfer ----
            $a2Ref = $nextRef++;
            $a2 = Orders::create([
                'user_id'            => $alice->id,
                'order_id'           => (string) $a2Ref,
                'shipfrom'           => 'Berlin, Germany',
                'shipto'             => 'Lahore, Pakistan',
                'postalcode'         => '54000',
                'address'            => '[TEST] Demo Street 2, Lahore, Pakistan',
                'approximate_weight' => '120',
                'product_services'   => 1,
                'product_purchase'   => 0,
                'total'              => '120',
                'order_status'       => 'Offer Accepted',
                'confirmation'       => 0,
                'custom_category'    => 'heavy_cargo',
            ]);

            Offerorder::create([
                'order_id'       => $a2->id,
                'shipingaddress' => '[TEST] Lahore, Pakistan',
                'description'    => '[TEST] DPTEST demo offer for heavy cargo custom order (accepted).',
                'total'          => 120,
                'product_total'  => 120,
                'offer_status'   => 1, // accepted
            ]);

            $paymentBt = Payment::create([
                'reference'           => 'DPTEST-BT-1',
                'order_id'            => $a2->id,
                'user_id'             => $alice->id,
                'attempt'             => 1,
                'amount'              => 120.00,
                'amount_refunded'     => 0,
                'currency'            => 'USD',
                'payment_method_id'   => $methods['bank_transfer']->id,
                'payment_method_code' => 'bank_transfer',
                'gateway'             => 'bank_transfer',
                'status'              => Payment::STATUS_AWAITING_PAYMENT,
                'status_set_by'       => 'system',
                'initiated_at'        => now(),
            ]);

            // ---- Order B1 (Bob, book-for-me): accepted offer + stripe ----
            $b1Ref = $nextRef++;
            $b1 = Orders::create([
                'user_id'            => $bob->id,
                'order_id'           => (string) $b1Ref,
                'shipfrom'           => 'New York, USA',
                'shipto'             => 'Karachi, Pakistan',
                'postalcode'         => '74200',
                'address'            => '[TEST] Demo Avenue 3, Karachi, Pakistan',
                'approximate_weight' => '8',
                'product_services'   => 0,
                'product_purchase'   => 1,
                'total'              => '64',
                'order_status'       => 'Offer Accepted',
                'confirmation'       => 0,
            ]);

            Offerorder::create([
                'order_id'       => $b1->id,
                'shipingaddress' => '[TEST] Karachi, Pakistan',
                'description'    => '[TEST] DPTEST demo offer for book-for-me order (accepted).',
                'total'          => 64,
                'product_total'  => 64,
                'offer_status'   => 1, // accepted
            ]);

            $paymentSt = Payment::create([
                'reference'           => 'DPTEST-ST-1',
                'order_id'            => $b1->id,
                'user_id'             => $bob->id,
                'attempt'             => 1,
                'amount'              => 64.00,
                'amount_refunded'     => 0,
                'currency'            => 'USD',
                'payment_method_id'   => $methods['stripe']->id,
                'payment_method_code' => 'stripe',
                'gateway'             => 'stripe',
                'status'              => Payment::STATUS_PROCESSING,
                'status_set_by'       => 'system',
                'initiated_at'        => now(),
                'metadata'            => ['gateway_kind' => 'mock'], // array cast on the model
            ]);

            // ---- Admin notification for order A1 (database channel only — no real email) ----
            $this->notifyAdminOfDemoOrder($a1, (string) $a1Ref);

            $this->summarize($alice, $bob, [
                ['ref' => $a1Ref, 'order' => $a1, 'flow' => 'ship_for_me', 'offer_status' => '0 (pending)', 'payment' => null],
                ['ref' => $a2Ref, 'order' => $a2, 'flow' => 'custom (heavy_cargo)', 'offer_status' => '1 (accepted)', 'payment' => $paymentBt],
                ['ref' => $b1Ref, 'order' => $b1, 'flow' => 'book_for_me (product_purchase=1)', 'offer_status' => '1 (accepted)', 'payment' => $paymentSt],
            ], $removed);
        });

        $this->info('[TEST] dp:seed-demo completed successfully.');

        return 0;
    }

    /**
     * Delete ONLY rows created by a previous run of this command.
     * Identified by: user email LIKE %@dptest.local, and the DPTEST
     * payload marker in TaskNotification data. Nothing else is touched.
     */
    private function cleanupPreviousRun(): array
    {
        $userIds = User::where('email', 'like', '%' . self::EMAIL_MARKER)->pluck('id');

        $removed = [
            'users'          => $userIds->count(),
            'orders'         => 0,
            'orderproducts'  => 0,
            'offerorders'    => 0,
            'payments'       => 0,
            'notifications'  => 0,
        ];

        if ($userIds->isNotEmpty()) {
            $orderIds = Orders::whereIn('user_id', $userIds)->pluck('id');

            $removed['orders']        = $orderIds->count();
            $removed['orderproducts'] = Orderproducts::whereIn('order_id', $orderIds)->count();
            $removed['offerorders']   = Offerorder::whereIn('order_id', $orderIds)->count();
            $removed['payments']      = Payment::whereIn('order_id', $orderIds)->count();
            $removed['notifications'] = DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->whereIn('notifiable_id', $userIds)
                ->count();

            DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->whereIn('notifiable_id', $userIds)
                ->delete();

            Payment::whereIn('order_id', $orderIds)->delete();
            Offerorder::whereIn('order_id', $orderIds)->delete();
            Orderproducts::whereIn('order_id', $orderIds)->delete();
            Orders::whereIn('user_id', $userIds)->delete();
            DB::table('users_roles')->whereIn('user_id', $userIds)->delete();
            User::whereIn('id', $userIds)->delete();
        }

        // Admin-side demo notifications (notifiable is an admin, not a demo user)
        // are matched by the DPTEST marker inside the TaskNotification payload.
        $removed['notifications'] += DB::table('notifications')
            ->where('type', TaskNotification::class)
            ->where('data', 'like', '%' . self::PAYLOAD_MARKER . '%')
            ->delete();

        return $removed;
    }

    /**
     * Ensure the payment method catalogue rows exist (config data, kept
     * across runs — not deleted by cleanup). firstOrCreate = idempotent.
     */
    private function ensurePaymentMethods(): array
    {
        $stripe = PaymentMethod::firstOrCreate(
            ['code' => 'stripe'],
            [
                'name'        => 'Credit/Debit Card (Stripe)',
                'gateway'     => 'stripe',
                'description' => 'Pay with card via Stripe.',
                'is_enabled'  => true,
                'priority'    => 10,
            ]
        );

        $bankTransfer = PaymentMethod::firstOrCreate(
            ['code' => 'bank_transfer'],
            [
                'name'        => 'Bank Transfer',
                'gateway'     => 'bank_transfer',
                'description' => 'Wire the amount to our bank account and upload the receipt.',
                'is_enabled'  => true,
                'priority'    => 20,
            ]
        );

        return ['stripe' => $stripe, 'bank_transfer' => $bankTransfer];
    }

    private function createDemoUser(string $name, string $email, string $phone, Role $clientRole): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name'     => $name,
            'number'   => $phone,
            'type'     => 'client',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'status'   => 'active',
        ]);
        $user->save();

        if (!$user->roles->contains($clientRole->id)) {
            $user->roles()->attach($clientRole->id);
        }

        return $user->fresh();
    }

    /** Next sequential order ref (orders.order_id is a varchar of integers). */
    private function nextOrderRef(): int
    {
        $max = (int) DB::table('orders')->max(DB::raw('CAST(order_id AS UNSIGNED)'));

        return $max + 1;
    }

    /**
     * Notify the first admin about demo order A1. Delivered on the database
     * channel ONLY (notifyNow with explicit channel list) so no real email
     * is sent from a seeding command.
     */
    private function notifyAdminOfDemoOrder(Orders $order, string $orderRef): void
    {
        $admin = User::where('type', 'admin')->orderBy('id')->first();

        if (!$admin) {
            $this->warn('No admin user found — skipping demo notification.');

            return;
        }

        try {
            $admin->notifyNow(new TaskNotification([
                'title'       => '[TEST] DPTEST new demo order received',
                'order_number' => $orderRef,
                'greeting'    => 'New [TEST] demo order',
                'order_id'    => $order->id, // must ALWAYS be present — the views require it
                'description' => '[TEST] DPTEST demo order A1 (ship-for-me) seeded by dp:seed-demo for order/payment flow testing.',
            ]), ['database']);
            $this->info("Notified admin #{$admin->id} ({$admin->email}) about demo order {$orderRef}.");
        } catch (\Throwable $e) {
            $this->warn('Admin notification failed (demo data itself is intact): ' . $e->getMessage());
        }
    }

    private function summarize(User $alice, User $bob, array $orders, array $removed): void
    {
        if (array_sum($removed) > 0) {
            $this->info(sprintf(
                'Cleanup of previous run: %d user(s), %d order(s), %d product(s), %d offer(s), %d payment(s), %d notification(s) removed.',
                $removed['users'],
                $removed['orders'],
                $removed['orderproducts'],
                $removed['offerorders'],
                $removed['payments'],
                $removed['notifications']
            ));
        } else {
            $this->info('Cleanup of previous run: nothing to remove (first run).');
        }

        $this->info('');
        $this->info('=== [TEST] DEMO USERS (password for all: ' . self::DEMO_PASSWORD . ') ===');
        $this->table(
            ['User', 'Email', 'Password', 'Type', 'Role'],
            [
                [$alice->name, $alice->email, self::DEMO_PASSWORD, $alice->type, 'client'],
                [$bob->name, $bob->email, self::DEMO_PASSWORD, $bob->type, 'client'],
            ]
        );

        $this->info('');
        $this->info('=== [TEST] DEMO ORDERS / OFFERS / PAYMENTS ===');
        $rows = [];
        foreach ($orders as $o) {
            /** @var Orders $order */
            $order = $o['order'];
            $rows[] = [
                (string) $o['ref'],
                $order->id,
                $order->user_id,
                $order->shipfrom . ' -> ' . $order->shipto,
                $order->order_status,
                $order->product_purchase ? 'book_for_me' : $o['flow'],
                $o['offer_status'],
                $o['payment'] ? $o['payment']->reference : '-',
                $o['payment'] ? $o['payment']->payment_method_code : '-',
                $o['payment'] ? $o['payment']->amount . ' ' . $o['payment']->currency : '-',
                $o['payment'] ? $o['payment']->status : '-',
            ];
        }
        $this->table(
            ['order_id(ref)', 'PK', 'user_id', 'route', 'status', 'flow', 'offer_status', 'payment ref', 'method', 'amount', 'payment status'],
            $rows
        );
    }
}
