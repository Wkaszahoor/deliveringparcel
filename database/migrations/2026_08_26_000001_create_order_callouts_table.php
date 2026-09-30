<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-manageable order-status callouts (CRUD). Replaces the hardcoded
 * "Callouts" cards in clients/order_offer.blade.php: the migration seeds
 * the EXACT legacy texts so nothing changes visually until an admin edits
 * them (Admin → Content → Order Callouts).
 *
 * status_key values in use (see App\Models\OrderCallout::SLOTS):
 *   offer_pending, forwarding_no_tracking, forwarding_tracking_added,
 *   purchase_no_tracking, ready_to_ship, shipped
 * Any other slug (e.g. a raw order_status like "Order processing") can be
 * added later for the new-design order pages.
 */
class CreateOrderCalloutsTable extends Migration
{
    public function up()
    {
        Schema::create('order_callouts', function (Blueprint $table) {
            $table->id();
            $table->string('status_key', 100);
            $table->string('style', 20)->default('info'); // danger|warning|info|success
            $table->string('heading', 255)->nullable();
            $table->text('body');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->index(['status_key', 'is_enabled']);
        });

        $now = now();
        $rows = [
            // ---- offer_pending (tab 1, offer awaiting accept/reject) ----
            ['offer_pending', 'danger', 'Hi there.',
             "we will be happy to help you get your package. Sending you Our offer including all cost. If it sounds good, please click ‘Accept offer’.\nLooking forward to shipping your order!", 10],
            ['offer_pending', 'info', null,
             'Otherwise you can decline the offer and get back to us if you think service fee and shipping fees are bit high for your budget , we will certainly help you try to accommodate you and give you the best price according to your budget ', 20],

            // ---- forwarding_no_tracking (accepted, forwarding, no tracking yet) ----
            ['forwarding_no_tracking', 'danger', 'Hi there.',
             'As you have selected only "Forwarding services". you can proceed and order your items on the given deliveringparcel residential address and add tracking details here so we can easily follow up the shipment.', 10],
            ['forwarding_no_tracking', 'info', null,
             'Please make sure you have put on all the credentials and information properly and correctly. we strongly recommended to use the same information in order to avoid confusion and conflicts with shipments.', 20],
            ['forwarding_no_tracking', 'warning', null,
             'Please do instruct the couriers to use door to door service and with authorised signature release. we will not be responsible if the shipment is dropped off to pick up point or dropped at mailbox/mailroom.', 30],

            // ---- forwarding_tracking_added ----
            ['forwarding_tracking_added', 'danger', 'Hi there.',
             'As you have ordered items and place tracking details. Once we receive we receive items we will send you a confirmation. ', 10],

            // ---- purchase_no_tracking (accepted, purchase assistance) ----
            ['purchase_no_tracking', 'danger', 'Hi there.',
             'As you have opted to go for "Purchase Assistance". we will purchase the given items for you within 2 working days.', 10],
            ['purchase_no_tracking', 'info', null,
             'In case of limited stock or unavailability, we will refund you the amount charged for the items which can not be purchase in given time.', 20],

            // ---- ready_to_ship (confirmation == 2) ----
            ['ready_to_ship', 'danger', 'Hi there.',
             '“ your items have been packed according to the services , if you are content and happy with the services provided , please confirm the item and package to be shipped so we can get this shipment on its way to your given destination “ ', 10],
            ['ready_to_ship', 'info', null,
             'Incase you need to change the address or need to instruct us with any thing , you can leave a note for us in chat box or get intouch with us any time quoting the order number “', 20],

            // ---- shipped (tab 4 static card) ----
            ['shipped', 'danger', null, '“ Please confirm when you receive the package . “ ', 10],
            ['shipped', 'info', null, 'And please do let us know how you feel about our service provided with your honest review . ', 20],
        ];

        foreach ($rows as [$key, $style, $heading, $body, $sort]) {
            DB::table('order_callouts')->insert([
                'status_key'  => $key,
                'style'       => $style,
                'heading'     => $heading,
                'body'        => $body,
                'sort_order'  => $sort,
                'is_enabled'  => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('order_callouts');
    }
}
