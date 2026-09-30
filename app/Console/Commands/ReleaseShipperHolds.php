<?php

namespace App\Console\Commands;

use App\Models\ShipperOrderAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Releases the 20% dispute hold back to the shipper's available balance
 * once hold_release_at is due (set to completion + 7 days at release time).
 * Safe to run repeatedly: an assignment's hold is released exactly once —
 * hold_release_at is nulled and a hold_release wallet transaction is written.
 */
class ReleaseShipperHolds extends Command
{
    protected $signature   = 'shipper:release-holds';
    protected $description = 'Release due 20% dispute holds to shipper wallets (7 days post-completion)';

    public function handle(): int
    {
        $due = ShipperOrderAssignment::where('status', 'completed')
            ->whereNotNull('hold_release_at')
            ->where('hold_release_at', '<=', now())
            ->where('wallet_hold_amount', '>', 0)
            ->get();

        if ($due->isEmpty()) {
            $this->info('No due holds.');

            return 0;
        }

        $released = 0;
        foreach ($due as $assignment) {
            DB::transaction(function () use ($assignment, &$released) {
                // Re-check inside the transaction to avoid double release on races.
                $fresh = ShipperOrderAssignment::where('id', $assignment->id)
                    ->whereNotNull('hold_release_at')
                    ->where('hold_release_at', '<=', now())
                    ->lockForUpdate()
                    ->first();
                if (!$fresh || (float) $fresh->wallet_hold_amount <= 0) {
                    return;
                }

                $shipper = $fresh->shipper;
                if (!$shipper) {
                    return;
                }

                $shipper->releaseHold((float) $fresh->wallet_hold_amount, $fresh->id);

                $fresh->update(['hold_release_at' => null]);
                $released++;
            });
        }

        $this->info("Released {$released} hold(s).");

        return 0;
    }
}
