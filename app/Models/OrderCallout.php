<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Admin-manageable order-status callout (clients/order_offer pages).
 *
 * Rendering contract (used by partials/order-callouts.blade.php):
 *  - table MISSING (migration not yet run)  → legacy default texts
 *  - table present, rows for a key          → those rows (admin truth)
 *  - table present, no rows for a key       → renders nothing (admin hid it)
 */
class OrderCallout extends Model
{
    protected $fillable = ['status_key', 'style', 'heading', 'body', 'sort_order', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    /** Admin-facing slot keys (legacy order page states) + descriptions. */
    public const SLOTS = [
        'offer_pending'              => 'Offer pending — awaiting customer Accept / Reject (tab 1)',
        'forwarding_no_tracking'     => 'Offer accepted, Forwarding — tracking details not added yet',
        'forwarding_tracking_added'  => 'Offer accepted, Forwarding — tracking details added',
        'purchase_no_tracking'       => 'Offer accepted, Purchase Assistance — tracking details not added yet',
        'ready_to_ship'              => 'Ready to ship — needs customer shipping confirmation',
        'shipped'                    => 'Shipped / delivery stage (tab 4)',
    ];

    public const STYLES = ['danger', 'warning', 'info', 'success'];

    /**
     * Legacy hardcoded texts — the exact copy that used to live inline in
     * clients/order_offer.blade.php. Served ONLY while the order_callouts
     * table does not exist, so the page is pixel-identical before/after
     * deploying the code but before running the migration.
     */
    public const LEGACY_DEFAULTS = [
        'offer_pending' => [
            ['style' => 'danger', 'heading' => 'Hi there.', 'body' => "we will be happy to help you get your package. Sending you Our offer including all cost. If it sounds good, please click ‘Accept offer’.\nLooking forward to shipping your order!"],
            ['style' => 'info', 'heading' => null, 'body' => 'Otherwise you can decline the offer and get back to us if you think service fee and shipping fees are bit high for your budget , we will certainly help you try to accommodate you and give you the best price according to your budget '],
        ],
        'forwarding_no_tracking' => [
            ['style' => 'danger', 'heading' => 'Hi there.', 'body' => 'As you have selected only "Forwarding services". you can proceed and order your items on the given deliveringparcel residential address and add tracking details here so we can easily follow up the shipment.'],
            ['style' => 'info', 'heading' => null, 'body' => 'Please make sure you have put on all the credentials and information properly and correctly. we strongly recommended to use the same information in order to avoid confusion and conflicts with shipments.'],
            ['style' => 'warning', 'heading' => null, 'body' => 'Please do instruct the couriers to use door to door service and with authorised signature release. we will not be responsible if the shipment is dropped off to pick up point or dropped at mailbox/mailroom.'],
        ],
        'forwarding_tracking_added' => [
            ['style' => 'danger', 'heading' => 'Hi there.', 'body' => 'As you have ordered items and place tracking details. Once we receive we receive items we will send you a confirmation. '],
        ],
        'purchase_no_tracking' => [
            ['style' => 'danger', 'heading' => 'Hi there.', 'body' => 'As you have opted to go for "Purchase Assistance". we will purchase the given items for you within 2 working days.'],
            ['style' => 'info', 'heading' => null, 'body' => 'In case of limited stock or unavailability, we will refund you the amount charged for the items which can not be purchase in given time.'],
        ],
        'ready_to_ship' => [
            ['style' => 'danger', 'heading' => 'Hi there.', 'body' => '“ your items have been packed according to the services , if you are content and happy with the services provided , please confirm the item and package to be shipped so we can get this shipment on its way to your given destination “ '],
            ['style' => 'info', 'heading' => null, 'body' => 'Incase you need to change the address or need to instruct us with any thing , you can leave a note for us in chat box or get intouch with us any time quoting the order number “'],
        ],
        'shipped' => [
            ['style' => 'danger', 'heading' => null, 'body' => '“ Please confirm when you receive the package . “ '],
            ['style' => 'info', 'heading' => null, 'body' => 'And please do let us know how you feel about our service provided with your honest review . '],
        ],
    ];

    /**
     * Rows to render for a slot/status key, or null when nothing should
     * render. Newlines in `body` become separate <p> paragraphs.
     */
    public static function forSlot(string $key): ?array
    {
        if ($key === '') {
            return null;
        }

        try {
            $rows = self::query()
                ->where('status_key', $key)
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        } catch (QueryException $e) {
            // Table not migrated yet — legacy hardcoded texts.
            return self::LEGACY_DEFAULTS[$key] ?? null;
        }

        if ($rows->isEmpty()) {
            return null;
        }

        return $rows->map(function (self $r) {
            return ['style' => $r->style, 'heading' => $r->heading, 'body' => $r->body];
        })->all();
    }
}
