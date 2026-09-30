<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\Orderproducts;
use App\Models\Orders;
use Illuminate\Http\Request;

/**
 * RQ-002 — multi-step shipping-request wizard (3 steps, one form, JS navigation).
 * All heavy fields live on the `orders` row; ownership is set server-side.
 */
class WizardController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        return view('home2.wizard.create', [
            'prefill' => [
                'name'    => $user->name ?? '',
                'email'   => $user->email ?? '',
                'phone'   => $user->ship_number ?? $user->phone ?? '',
                'address' => $user->ship_address1 ?? '',
                'city'    => $user->ship_city ?? '',
                'country' => $user->ship_country ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipfrom'            => 'required|string|max:120',
            'shipto'              => 'required|string|max:120',
            'address'             => 'required|string|max:500',
            'postalcode'          => 'required|string|max:20',
            'approximate_weight'  => 'required|numeric|min:0.1|max:20000',
            'product_description' => 'required|string|min:5|max:2000',
            'product_photo'       => 'nullable|url|max:500',
            'product_services'    => 'nullable|array',
            'product_services.*'  => 'nullable|string|max:60',
            'notes'               => 'nullable|string|max:2000',
        ]);

        /* Public sequential reference (audit-friendly, not a secret).
           order_id is a VARCHAR column — plain max() compares STRINGS, so a
           '9xxx' row beats '11133' and the sequence sticks at 10000 forever.
           CAST forces a numeric max. */
        $next = (int) Orders::max(\DB::raw('CAST(order_id AS UNSIGNED)')) + 1;

                /* Service selections map to the per-service int columns on orders
           (product_services is an int column — an imploded list would be
           silently truncated to 0 by MySQL). */
        $svc = array_flip($data['product_services'] ?? []);

        $order = Orders::create([
            'user_id'              => $request->user()->id,
            'order_id'             => $next,
            'shipfrom'             => $data['shipfrom'],
            'shipto'               => $data['shipto'],
            'address'              => $data['address'],
            'postalcode'           => $data['postalcode'],
            'approximate_weight'   => $data['approximate_weight'],
            'product_disinfection'  => isset($svc['disinfection']) ? 1 : 0,
            'product_consolidation' => isset($svc['consolidation']) ? 1 : 0,
            'product_customs'       => isset($svc['customs']) ? 1 : 0,
            'product_check'         => isset($svc['product_check']) ? 1 : 0,
            'product_photo'        => 0, // int flag column on orders; the link itself is kept on the product row
            'order_status'         => 'pending',
            'confirmation'         => 0,
        ]);

        /* orders has no product_description column — the description (plus
           notes and photo link) is stored as an orderproducts row, matching
           the legacy and /home2/request2 request flows. */
        $description = trim($data['product_description']);
        if (!empty($data['notes'])) {
            $description .= "\n\nNotes: " . $data['notes'];
        }
        if (!empty($data['product_photo'])) {
            $description .= "\nPhoto: " . $data['product_photo'];
        }
        Orderproducts::create([
            'order_id'        => $order->id,
            'productname'     => $description,
            'producturl'      => '',
            'productquantity' => 1,
            'productweight'   => $data['approximate_weight'],
        ]);

        return redirect()->route('home2.dashboard')->with('success', 'Request #' . $order->order_id . ' submitted — we will send an offer shortly.');
    }
}
