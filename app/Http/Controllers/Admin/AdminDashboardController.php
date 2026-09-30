<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GET /admin-dashboard2 — executive KPI dashboard (moved here so
 * /admin-dashboard keeps its original redirect to the legacy dashboard;
 * the legacy /admin-orders flow is untouched). READ-ONLY against the
 * real schema. Every section computes inside try/catch — one broken
 * metric can never blank the whole page.
 */
class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user() && $request->user()->type === 'admin', 403);

        $range = (string) $request->input('range', '30');
        if (!in_array($range, ['today', '7', '30', '90', 'year'], true)) {
            $range = '30';
        }

        [$from, $to, $prevFrom, $prevTo] = $this->periods($range);

        return view('admin.kpi_dashboard', [
            'range'      => $range,
            'from'       => $from,
            'to'         => $to,
            'kpi'        => $this->headline($from, $to, $prevFrom, $prevTo),
            'action'     => $this->actionRequired(),
            'payStatus'  => $this->guard(fn () => $this->paymentStatuses($from, $to), []),
            'providers'  => $this->guard(fn () => $this->providers($from, $to), []),
            'funnel'     => $this->guard(fn () => $this->funnel(), []),
            'customers'  => $this->guard(fn () => $this->customers($from, $to), []),
            'ops'        => $this->guard(fn () => $this->operations(), []),
            'trend'      => $this->guard(fn () => $this->trend($from, $to, $range), null),
            'extra'      => $this->extraGuarded(),
        ]);
    }

    /* ───────────────────────── helpers ───────────────────────── */

    private function guard(callable $fn, $fallback)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);
            return $fallback;
        }
    }

    private function periods(string $range): array
    {
        $now = now();
        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy(), $now->copy()->subDay()->startOfDay(), $now->copy()->startOfDay()];
            case 'year':
                return [$now->copy()->startOfYear(), $now->copy(), $now->copy()->subYear()->startOfYear(), $now->copy()->startOfYear()];
            case '7':
                $d = 7; break;
            case '90':
                $d = 90; break;
            default:
                $d = 30;
        }
        return [
            $now->copy()->subDays($d), $now->copy(),
            $now->copy()->subDays($d * 2), $now->copy()->subDays($d),
        ];
    }

    private function delta(?float $curr, ?float $prev): ?float
    {
        if ($prev === null || $prev == 0) {
            return null;
        }
        return round((($curr - $prev) / abs($prev)) * 100, 1);
    }

    private function headline($from, $to, $prevFrom, $prevTo): array
    {
        $rev  = fn ($a, $b) => (float) DB::table('payments')->where('status', 'paid')->whereBetween('created_at', [$a, $b])->sum('amount');
        $ord  = fn ($a, $b) => (int) DB::table('orders')->whereNull('archived_at')->whereBetween('created_at', [$a, $b])->count();
        $cust = fn ($a, $b) => (int) DB::table('users')->where('type', 'client')->whereBetween('created_at', [$a, $b])->count();

        $curRev = $rev($from, $to);
        $paidN  = (int) DB::table('payments')->where('status', 'paid')->whereBetween('created_at', [$from, $to])->count();

        return [
            'revenue'       => ['v' => $curRev, 'd' => $this->delta($curRev, $rev($prevFrom, $prevTo))],
            'orders'        => ['v' => $ord($from, $to), 'd' => $this->delta($ord($from, $to), $ord($prevFrom, $prevTo))],
            'new_customers' => ['v' => $cust($from, $to), 'd' => $this->delta($cust($from, $to), $cust($prevFrom, $prevTo))],
            'avg_order'     => ['v' => $paidN > 0 ? round($curRev / $paidN, 2) : 0, 'd' => null],
        ];
    }

    private function actionRequired(): array
    {
        return $this->guard(function () {
            $noOffer = (int) DB::table('orders as o')->whereNull('o.archived_at')
                ->whereNotIn('o.order_status', ['completed', 'received'])
                ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('offerorders as f')->whereColumn('f.order_id', 'o.id'))
                ->count();

            $trackingNeeded = (int) DB::table('orders as o')->whereNull('o.archived_at')
                ->where('o.tracking_status', 0)
                ->whereExists(fn ($q) => $q->selectRaw(1)->from('payments as p')
                    ->whereColumn('p.order_id', 'o.id')->where('p.status', 'paid'))
                ->count();

            return [
                'verify_payments'  => (int) DB::table('payments')->where('status', 'awaiting_verification')->count(),
                'awaiting_payment' => (int) DB::table('payments')->where('status', 'awaiting_payment')->count(),
                'no_offer'         => $noOffer,
                'offer_rejected'   => (int) DB::table('orders')->where('order_status', 'Offer Rejected')->whereNull('archived_at')->count(),
                'failed_payments'  => (int) DB::table('payments')->where('status', 'failed')->count(),
                'tracking_needed'  => $trackingNeeded,
                'pending_reviews'  => Schema::hasTable('reviews') ? (int) DB::table('reviews')->where('status', 'pending')->count() : 0,
            ];
        }, []);
    }
    private function paymentStatuses($from, $to): array
    {
        $rows = DB::table('payments')->whereBetween('created_at', [$from, $to])
            ->selectRaw("status, COUNT(*) n, COALESCE(SUM(amount),0) amt, COALESCE(SUM(amount_refunded),0) refunded")
            ->groupBy('status')->get()->keyBy('status');

        $get = fn ($s) => ['n' => (int) ($rows[$s]->n ?? 0), 'amt' => (float) ($rows[$s]->amt ?? 0)];

        return [
            'paid'         => $get('paid'),
            'awaiting'     => $get('awaiting_payment'),
            'verifying'    => $get('awaiting_verification'),
            'processing'   => $get('processing'),
            'failed'       => $get('failed'),
            'refunded_amt' => (float) $rows->sum('refunded'),
        ];
    }

    private function providers($from, $to): array
    {
        return DB::table('payments')->whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(NULLIF(gateway,''),'unknown') gw, COUNT(*) n,
                COALESCE(SUM(CASE WHEN status='paid' THEN amount END),0) volume,
                SUM(status='failed') failed_n,
                COALESCE(SUM(CASE WHEN status='refunded' THEN amount END),0) refunded")
            ->groupBy('gw')->orderByDesc('volume')->get()
            ->map(fn ($r) => [
                'gw' => $r->gw, 'n' => (int) $r->n, 'volume' => (float) $r->volume,
                'failed' => (int) $r->failed_n, 'refunded' => (float) $r->refunded,
                'health' => $r->failed_n > 0 && $r->n > 0 && ($r->failed_n / $r->n) > 0.15 ? 'warn' : 'ok',
            ])->all();
    }
    private function funnel(): array
    {
        $orders   = (int) DB::table('orders')->whereNull('archived_at')->count();
        $offered  = (int) DB::table('orders as o')->whereNull('o.archived_at')
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('offerorders as f')->whereColumn('f.order_id', 'o.id'))->count();
        $accepted = (int) DB::table('orders')->whereNull('archived_at')
            ->whereIn('order_status', ['Offer Accepted', 'Order placed', 'Confirm Shipment', 'Order processing', 'completed', 'received'])->count();
        $paid     = (int) DB::table('orders as o')
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('payments as p')->whereColumn('p.order_id', 'o.id')->where('p.status', 'paid'))->count();
        $pkgRecv  = (int) DB::table('orders')->whereNull('archived_at')->where('tracking_status', 1)->count();
        $done     = (int) DB::table('orders')->whereNull('archived_at')->whereIn('order_status', ['completed', 'received'])->count();
        $received = (int) DB::table('orders')->whereNull('archived_at')->where('order_status', 'received')->count();

        $pct = fn ($a, $b) => $b > 0 ? round($a / $b * 100, 1) : 0;

        return [
            'steps' => [
                ['Orders created', $orders, 100],
                ['Offer sent', $offered, $pct($offered, $orders)],
                ['Offer accepted', $accepted, $pct($accepted, $offered)],
                ['Paid', $paid, $pct($paid, $accepted)],
                ['Package received', $pkgRecv, $pct($pkgRecv, $paid)],
                ['Delivered (completed)', $done, $pct($done, $pkgRecv)],
                ['Receipt confirmed', $received, $pct($received, $done)],
            ],
            'order_to_done' => $pct($done, $orders),
        ];
    }
    private function customers($from, $to): array
    {
        $total = (int) DB::table('users')->where('type', 'client')->count();
        $new   = (int) DB::table('users')->where('type', 'client')->whereBetween('created_at', [$from, $to])->count();
        $active = (int) DB::table('users as u')->where('u.type', 'client')
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('orders as o')
                ->whereColumn('o.user_id', 'u.id')->whereNotIn('o.order_status', ['completed', 'received']))->count();
        $repeat = (int) DB::table('orders')->whereNull('archived_at')
            ->selectRaw('user_id')->groupBy('user_id')->havingRaw('COUNT(*) >= 2')->get()->count();

        return [
            'total' => $total, 'new' => $new, 'active' => $active, 'repeat' => $repeat,
            'avg_orders' => $total > 0 ? round((int) DB::table('orders')->whereNull('archived_at')->count() / $total, 1) : 0,
        ];
    }

    private function operations(): array
    {
        $inTransit = (int) DB::table('orders')->whereNull('archived_at')
            ->whereIn('order_status', ['Order processing', 'Confirm Shipment', 'Ready To Ship'])->count();
        $completed = (int) DB::table('orders')->whereNull('archived_at')->where('order_status', 'completed')->count();
        $received  = (int) DB::table('orders')->whereNull('archived_at')->where('order_status', 'received')->count();
        $deliveredToday = (int) DB::table('orders')->whereNull('archived_at')
            ->whereIn('order_status', ['completed', 'received'])->whereDate('updated_at', today())->count();
        $avgDays = DB::table('orders')->whereNull('archived_at')
            ->whereIn('order_status', ['completed', 'received'])
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) d')->value('d');

        return [
            'in_transit' => $inTransit, 'completed' => $completed, 'received' => $received,
            'delivered_today' => $deliveredToday,
            'avg_delivery_days' => $avgDays !== null ? round((float) $avgDays, 1) : null,
        ];
    }
    private function trend($from, $to, string $range): ?array
    {
        $month = $range === 'year';
        $fmt   = $month ? '%Y-%m' : '%Y-%m-%d';

        $orders = DB::table('orders')->whereNull('archived_at')->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') d, COUNT(*) n")->groupBy('d')->pluck('n', 'd');
        $revenue = DB::table('payments')->where('status', 'paid')->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') d, SUM(amount) n")->groupBy('d')->pluck('n', 'd');

        $labels = [];
        $keys = [];
        $ordSeries = [];
        $revSeries = [];
        $cursor = $from->copy();
        while ($cursor <= $to) {
            $key = $cursor->format($month ? 'Y-m' : 'Y-m-d');
            $keys[] = $key;
            $labels[] = $month ? ($cursor->englishMonth . ' ' . $cursor->format('y')) : $cursor->format('d M');
            $ordSeries[] = (int) ($orders[$key] ?? 0);
            $revSeries[] = round((float) ($revenue[$key] ?? 0), 2);
            $cursor = $month ? $cursor->addMonth() : $cursor->addDay();
            if (count($labels) > 400) break;
        }

        return ['labels' => $labels, 'orders' => $ordSeries, 'revenue' => $revSeries];
    }

    /** Optional tables — present only on newer installs; fully guarded. */
    private function extraGuarded(): array
    {
        $out = ['returns_open' => null, 'claims_open' => null, 'failed_jobs' => null];
        try {
            if (Schema::hasTable('return_requests')) {
                $out['returns_open'] = (int) DB::table('return_requests')
                    ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])->count();
            }
        } catch (\Throwable $e) { report($e); }
        try {
            if (Schema::hasTable('claims')) {
                $out['claims_open'] = (int) DB::table('claims')
                    ->whereNotIn('status', ['closed', 'resolved', 'rejected'])->count();
            }
        } catch (\Throwable $e) { report($e); }
        try {
            if (Schema::hasTable('failed_jobs')) {
                $out['failed_jobs'] = (int) DB::table('failed_jobs')->count();
            }
        } catch (\Throwable $e) { report($e); }
        return $out;
    }
}
