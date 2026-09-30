<?php

namespace App\Http\Controllers\Admin\Quotes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Quote inbox (request_quotes) + offer/negotiation read-model.
 */
class QuoteController extends Controller
{
    public function quotesIndex()
    {
        return view('admin.quotes.index');
    }

    public function quotesData(Request $request)
    {
        $query = DB::table('request_quotes');

        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('country', 'like', $like)
                    ->orWhere('destination', 'like', $like);
            });
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from') . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to') . ' 23:59:59');
        }

        $query->orderByDesc('created_at');

        return response()->json(
            $query->paginate(dp_per_page($request))->through(function ($r) {
                return [
                    'id'          => $r->id,
                    'name'        => $r->name,
                    'email'       => $r->email,
                    'number'      => $r->number,
                    'cargo'       => $r->cargotype,
                    'route'       => trim(($r->country ?: '?') . ' → ' . ($r->destination ?: '?')),
                    'weight'      => $r->weight,
                    'detail'      => \Str::limit(strip_tags((string) $r->detail), 80),
                    'created_at'  => optional($r->created_at)->format('M d, Y'),
                    'detail_url'  => route('admin.quotes.show', $r->id),
                ];
            })->toArray()
        );
    }

    public function show(int $id)
    {
        $quote = DB::table('request_quotes')->where('id', $id)->first();
        abort_if($quote === null, 404);

        return view('admin.quotes.show', [
            'quote'      => $quote,
            // PM-015: mark the gateway this quote's customer must pay with.
            'payMethods' => \App\Models\PaymentMethod::enabled()->orderBy('priority')->get(),
        ]);
    }

    /**
     * PM-015: set/clear the forced payment method for a quote request.
     * Displayed on the quote (guides the reply to the customer) and carried
     * onto an order created from this quote via orders.request_quote_id.
     */
    public function updatePaymentMethod(Request $request, int $id)
    {
        $validated = $request->validate([
            'method' => 'nullable|string|max:30',
            'note'   => 'nullable|string|max:255',
        ]);

        $quote = DB::table('request_quotes')->where('id', $id)->first();
        abort_if($quote === null, 404);

        $code = trim((string) ($validated['method'] ?? ''));

        if ($code === '') {
            DB::table('request_quotes')->where('id', $id)->update([
                'forced_payment_method_code' => null,
                'updated_at'                 => now(),
            ]);

            return response()->json(['ok' => true, 'cleared' => true, 'message' => 'Payment method requirement cleared for quote #' . $id . '.']);
        }

        $method = \App\Models\PaymentMethod::where('code', $code)->where('is_enabled', true)->first();
        if (!$method) {
            return response()->json(['ok' => false, 'message' => 'Unknown or disabled payment method: ' . $code], 422);
        }

        DB::table('request_quotes')->where('id', $id)->update([
            'forced_payment_method_code' => $method->code,
            'updated_at'                 => now(),
        ]);

        return response()->json([
            'ok'      => true,
            'cleared' => false,
            'message' => $method->name . ' is now the required payment method for quote #' . $id . '. Tell the customer in your reply.',
        ]);
    }

    /* ---------------- Offers / negotiation ---------------- */

    public function offersIndex()
    {
        return view('admin.quotes.offers');
    }

    public function offersData(Request $request)
    {
        $query = DB::table('offerorders as of')
            ->leftJoin('orders as o', 'o.id', '=', 'of.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->select(
                'of.id', 'of.order_id', 'of.offer_status', 'of.total', 'of.product_total',
                'of.rejections_note', 'of.created_at',
                'o.order_id as ref', 'u.name as user_name', 'u.email as user_email'
            );

        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                    ->orWhere('u.email', 'like', $like)
                    ->orWhere('o.order_id', 'like', $like);
            });
        }
        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('of.offer_status', (int) $request->input('status'));
        }

        $query->orderByDesc('of.created_at');

        /* Status meaning inferred from offer_status ints (1 accepted … per app convention). */
        $statusMap = config('admin_quotes.offer_status_map', [
            0 => ['New', 'bg-secondary'],
            1 => ['Accepted', 'bg-success'],
            2 => ['Rejected', 'bg-danger'],
            3 => ['Counter', 'bg-warning'],
        ]);

        return response()->json(
            $query->paginate(dp_per_page($request))->through(function ($r) use ($statusMap) {
                $meta = $statusMap[$r->offer_status] ?? ['Status ' . $r->offer_status, 'bg-info'];
                return [
                    'id'         => $r->id,
                    'order_id'   => $r->order_id,
                    'order_ref'  => $r->ref,
                    'user'       => $r->user_name ?: '—',
                    'email'      => $r->user_email,
                    'total'      => number_format((float) $r->total, 2),
                    'products'   => (int) $r->product_total,
                    'status_raw' => $r->offer_status,
                    'status'     => $meta[0],
                    'color'      => $meta[1],
                    'note'       => \Str::limit((string) $r->rejections_note, 60),
                    'created_at' => optional($r->created_at)->format('M d, Y'),
                    'detail_url' => url('admin-orders') . '#offer-' . $r->id,
                ];
            })->toArray()
        );
    }
}
