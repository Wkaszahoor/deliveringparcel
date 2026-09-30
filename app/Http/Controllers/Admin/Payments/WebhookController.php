<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use App\Services\Payments\StripeGateway;

/**
 * Agent WL — Stripe webhook history admin (PM-017).
 *
 * - index:   full local delivery history (filterable by type/status)
 * - show:    one event with the complete payload
 * - compare: pulls the latest events FROM the Stripe account (via the secret
 *            key) and shows, side by side, which ones we received + processed
 *            and which are MISSING locally (endpoint down / not delivered).
 */
class WebhookController extends Controller
{
    public function index()
    {
        $events = WebhookEvent::query()
            ->when(trim((string) request('type')) !== '', fn ($q) => $q->where('type', 'like', '%' . trim((string) request('type')) . '%'))
            ->when(trim((string) request('status')) !== '', fn ($q) => $q->where('status', trim((string) request('status'))))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.payments.webhooks', [
            'events' => $events,
            'statuses' => ['processed', 'ignored', 'received', 'signature_failed', 'error'],
        ]);
    }

    public function show(WebhookEvent $webhookEvent)
    {
        return view('admin.payments.webhook_show', ['event' => $webhookEvent]);
    }

    public function compare()
    {
        $gateway = app(StripeGateway::class);
        if (!$gateway->isConfigured()) {
            return view('admin.payments.webhooks_compare', ['stripeEvents' => null, 'local' => collect(), 'error' => 'Stripe is not configured on this environment (no secret key).']);
        }

        try {
            // Latest 25 events straight from the Stripe account (test or live mode
            // follows the key). Only the ids are needed for matching.
            $stripeEvents = $gateway->events()->all(['limit' => 25]);
            $stripeIds = collect($stripeEvents->data)->pluck('id')->all();
            $local = WebhookEvent::whereIn('event_id', $stripeIds)->get()->keyBy('event_id');

            return view('admin.payments.webhooks_compare', [
                'stripeEvents' => collect($stripeEvents->data),
                'local'        => $local,
                'error'        => null,
            ]);
        } catch (\Throwable $e) {
            return view('admin.payments.webhooks_compare', [
                'stripeEvents' => null,
                'local'        => collect(),
                'error'        => 'Could not reach Stripe: ' . $e->getMessage(),
            ]);
        }
    }
}
