<?php

namespace App\Services\Shipper;

use App\Models\Orders;
use App\Models\ShippingRequest;
use App\Models\ShipperProfile;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperQuote;
use App\Notifications\Shipper\NewShippingRequestNotification;
use App\Notifications\Shipper\ShipperSelectedNotification;
use App\Services\UsernameService;
use Illuminate\Support\Facades\DB;

class ShippingRequestService
{
    public function __construct(private UsernameService $usernameSvc) {}

    /** Auto-generate a masked brief from an existing order (1-click). */
    public function generateBriefFromOrder(int $orderId, int $adminId): array
    {
        $order    = Orders::with(['orderproducts', 'user'])->findOrFail($orderId);
        $products = $order->orderproducts ?? collect();

        $customerUsername = $order->user_id
            ? $this->usernameSvc->ensureCustomerUsername((int) $order->user_id)
            : 'CUS-GUEST';

        // Obfuscate exact value from shippers: ±30%, rounded to 10s.
        $totalValue = (float) $products->sum('price');
        $rangeMin   = floor($totalValue * 0.7 / 10) * 10;
        $rangeMax   = ceil($totalValue * 1.3 / 10) * 10;

        $serviceType = ((int) ($order->product_purchase ?? 0)) === 1 ? 'buy_for_me' : 'ship_for_me';

        $productDetails = $products->map(function ($p) {
            return [
                'name'            => $p->productname ?? '',
                'url'             => $p->url ?? $p->producturl ?? '',
                'quantity'        => (int) ($p->quantity ?? 1),
                'description'     => $p->productname ?? '',
                'estimated_value' => (float) ($p->price ?? 0),
                'weight'          => (float) ($p->weight ?? 0),
                'notes'           => $p->notes ?? '',
            ];
        })->values()->toArray();

        $servicesNeeded = [
            'photo_required'   => (bool) ($order->check ?? 0),
            'customs'          => (bool) ($order->customs ?? 0),
            'repack'           => (bool) ($order->consolidation ?? 0),
            'prohibited_check' => (bool) ($order->prohibited ?? 0),
            'disinfection'     => (bool) ($order->disinfection ?? 0),
            'purchase_assist'  => (bool) ($order->product_purchase ?? 0),
        ];

        $servicesList = collect($servicesNeeded)->filter()->keys()
            ->map(fn ($k) => '✓ ' . ucwords(str_replace('_', ' ', $k)))
            ->implode("\n");

        $productText = collect($productDetails)->map(function ($p, $i) {
            return 'Product ' . ($i + 1) . ": {$p['name']}\n"
                . "URL: {$p['url']}\n"
                . "Qty: {$p['quantity']} | Est. Value: \${$p['estimated_value']}"
                . ($p['weight'] ? " | Weight: {$p['weight']}kg" : '')
                . ($p['notes'] ? "\nNotes: {$p['notes']}" : '');
        })->implode("\n\n");

        $briefText = <<<TEXT
        SHIPPING REQUEST — AUTO GENERATED
        Customer Code : {$customerUsername}
        Service Type  : {$serviceType}
        Country Needed: {$order->shipfrom}

        ITEMS TO HANDLE:
        {$productText}

        SERVICES REQUIRED:
        {$servicesList}

        VALUE RANGE: \${$rangeMin} – \${$rangeMax}
        DEADLINE FOR RESPONSE: 48 hours

        NOTE: Do not contact customer directly.
        All communication through admin platform only.
        TEXT;

        // Canonical country = ISO-2 from the countries table — shippers'
        // service_countries stores ISO-2, so marketplace matching needs it.
        // Fall back to first-3 letters when the country is unknown to the table.
        $shipFromName = trim((string) $order->shipfrom);
        $countryRequired = 'XX';
        if ($shipFromName !== '') {
            // Legacy order forms stored free-text spellings - map them first.
            $aliases = [
                'germeny' => 'Germany', 'usa' => 'United States',
                'united state of america (usa)' => 'United States',
                'uk' => 'United Kingdom', 'united kingdom (uk)' => 'United Kingdom',
                'uae' => 'United Arab Emirates', 'united arab emirate (uae)' => 'United Arab Emirates',
            ];
            $resolved = $aliases[mb_strtolower($shipFromName)] ?? $shipFromName;
            $iso2 = DB::table('countries')->where('name', $resolved)->value('iso2');
            if (!$iso2) {
                $iso2 = DB::table('countries')->whereRaw('LOWER(name) = LOWER(?)', [$resolved])->value('iso2')
                    ?: DB::table('countries')->where('iso3', strtoupper(substr($shipFromName, 0, 3)))->value('iso2');
            }
            $countryRequired = strtoupper($iso2 ?: substr($resolved, 0, 3));
        }

        return [
            'order_id'          => $order->id,
            'customer_username' => $customerUsername,
            'service_type'      => $serviceType,
            'country_required'  => $countryRequired,
            'brief_text'        => $briefText,
            'product_details'   => $productDetails,
            'services_needed'   => $servicesNeeded,
            'value_range_min'   => $rangeMin,
            'value_range_max'   => $rangeMax,
            'address_snippet'   => (string) ($order->shipto ?? ''),
            'contact_email'     => '', // admin fills — never auto-fill
            'contact_phone'     => '',
            'required_level'    => $serviceType === 'ship_for_me' ? 2 : 1,
            'created_by'        => $adminId,
            'expires_at'        => now()->addHours(48),
        ];
    }

    /** Publish a shipping request to eligible shippers. */
    public function publishRequest(ShippingRequest $request): void
    {
        $request->update(['status' => 'open']);

        // Admin-blocked country: no shipper notifications, stays out of marketplaces.
        $countryEnabled = DB::table('countries')->where('iso2', $request->country_required)->where('is_active', 1)->exists();
        if (!$countryEnabled) {
            return;
        }

        $shippers = ShipperProfile::active()
            ->forCountry($request->country_required)
            ->level($request->required_level)
            ->hasCapacity()
            ->with('user')
            ->get();

        foreach ($shippers as $shipper) {
            if ($shipper->user) {
                $shipper->user->notify(new NewShippingRequestNotification($request));
            }
        }
    }

    /** Admin selects a shipper — creates the assignment transactionally. */
    public function assignShipper(
        ShippingRequest $request,
        ShipperProfile $shipper,
        float $shipperFee,
        float $platformFee,
        float $totalCharged,
        int $adminId
    ): ShipperOrderAssignment {
        return DB::transaction(function () use (
            $request, $shipper, $shipperFee, $platformFee, $totalCharged, $adminId
        ) {
            ShipperQuote::where('request_id', $request->id)
                ->where('shipper_profile_id', '!=', $shipper->id)
                ->update(['status' => 'rejected', 'responded_at' => now()]);

            ShipperQuote::where('request_id', $request->id)
                ->where('shipper_profile_id', $shipper->id)
                ->update(['status' => 'accepted', 'responded_at' => now()]);

            $holdAmount = round($shipperFee * 0.20, 2);
            $creditNow  = round($shipperFee - $holdAmount, 2);

            $assignment = ShipperOrderAssignment::create([
                'order_id'             => $request->order_id,
                'request_id'           => $request->id,
                'shipper_profile_id'   => $shipper->id,
                'shipper_fee'          => $shipperFee,
                'platform_fee'         => $platformFee,
                'total_charged'        => $totalCharged,
                'status'               => 'assigned',
                'purchase_deadline'    => $request->service_type === 'buy_for_me'
                    ? now()->addDays(2) : null,
                'wallet_credit_amount' => $creditNow,
                'wallet_hold_amount'   => $holdAmount,
                'admin_notes'          => 'Assigned by admin #' . $adminId,
            ]);

            $request->update([
                'status'                      => 'assigned',
                'is_frozen'                   => true,
                'assigned_shipper_profile_id' => $shipper->id,
                'assigned_at'                 => now(),
            ]);

            DB::table('orders')->where('id', $request->order_id)
                ->update(['has_shipper_assignment' => true]);

            $shipper->increment('current_active_orders');

            if ($shipper->user) {
                $shipper->user->notify(new ShipperSelectedNotification($assignment));
            }

            return $assignment;
        });
    }

    /** Release payment after the customer confirms delivery. */
    public function releasePayment(ShipperOrderAssignment $assignment): void
    {
        DB::transaction(function () use ($assignment) {
            $shipper = $assignment->shipper;

            $shipper->creditWallet(
                (float) $assignment->wallet_credit_amount,
                'Payment for Order #' . $assignment->order_id,
                $assignment->order_id,
                $assignment->id
            );

            $assignment->update([
                'status'          => 'completed',
                'completed_at'    => now(),
                'hold_release_at' => now()->addDays(7),
            ]);

            $shipper->holdAmount((float) $assignment->wallet_hold_amount, $assignment->id);

            $shipper->decrement('current_active_orders');
            $shipper->increment('total_completed');
            $shipper->fresh()->checkLevelProgression();

            DB::table('orders')->where('id', $assignment->order_id)
                ->update(['order_status' => 'completed']);
        });
    }
}
