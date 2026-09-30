<?php

namespace App\Http\Controllers\Admin\Analytics\Concerns;

use Illuminate\Support\Facades\DB;

/*
 * Shared revenue queries for the Analytics module (Agent A).
 *
 * Revenue = `offerorders.total` of offers ACCEPTED by the client
 * (offer_status = 1), deduplicated to one accepted offer per order.
 */
trait ComputesRevenue
{
    /**
     * offerorders.offer_status value meaning "accepted by client".
     */
    protected function acceptedOfferStatus(): int
    {
        return (int) config('admin_analytics.accepted_offer_status', 1);
    }

    /**
     * Subquery: one row per order that has an accepted offer.
     * Columns: order_id, amount (per-order MAX), offer_at (first accepted offer date).
     */
    protected function acceptedOffersPerOrder()
    {
        return DB::table('offerorders')
            ->selectRaw('order_id, MAX(total) AS amount, MIN(created_at) AS offer_at')
            ->where('offer_status', $this->acceptedOfferStatus())
            ->whereNotNull('created_at')
            ->groupBy('order_id');
    }

    /**
     * Query joining the per-order accepted offers with orders/users.
     * Exposes: ao.order_id, ao.amount, ao.offer_at, o.*, u.name, u.email
     */
    protected function acceptedOffersJoin()
    {
        return DB::query()
            ->fromSub($this->acceptedOffersPerOrder(), 'ao')
            ->join('orders as o', 'o.id', '=', 'ao.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id');
    }

    /**
     * Total accepted-offer revenue between two datetimes (inclusive).
     */
    protected function revenueBetween($from, $to): float
    {
        return (float) $this->acceptedOffersJoin()
            ->whereBetween('ao.offer_at', [$from, $to])
            ->sum('ao.amount');
    }
}
