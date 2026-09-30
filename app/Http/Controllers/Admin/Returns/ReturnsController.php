<?php

namespace App\Http\Controllers\Admin\Returns;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\Claim;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    public function returnsIndex()
    {
        $map = config('admin_quotes.return_statuses');

        return view('admin.returns.index', ['statusMap' => $map]);
    }

    public function returnsData(Request $request)
    {
        $query = ReturnRequest::query()->with('user:id,name,email');

        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('reason', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderByDesc('created_at');
        $map = config('admin_quotes.return_statuses');

        return response()->json(
            $query->paginate(dp_per_page($request))->through(function (ReturnRequest $r) use ($map) {
                $meta = $map[$r->status] ?? ['?', 'bg-secondary'];
                return [
                    'id'         => $r->id,
                    'user'       => optional($r->user)->name ?: '—',
                    'email'      => optional($r->user)->email,
                    'order_id'   => $r->order_id,
                    'reason'     => $r->reason,
                    'status'     => $r->status,
                    'status_lbl' => $meta['label'],
                    'color'      => $meta['color'],
                    'next'       => $meta['next'],
                    'created_at' => optional($r->created_at)->format('M d, Y'),
                    'urls'       => [
                        'show'   => route('admin.returns.show', $r->id),
                        'update' => route('admin.returns.status', $r->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function show(ReturnRequest $return)
    {
        $return->load('user:id,name,email', 'claims');

        return view('admin.returns.show', [
            'return'     => $return,
            'statusMap'  => config('admin_quotes.return_statuses'),
        ]);
    }

    public function updateStatus(Request $request, ReturnRequest $return)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(config('admin_quotes.return_statuses'))),
            'note'   => 'nullable|string|max:2000',
        ]);

        $allowed = config('admin_quotes.return_statuses.' . $return->status . '.next', []);
        if (!in_array($data['status'], $allowed, true)) {
            return redirect()->route('admin.returns.show', $return->id)
                ->with('error', 'Cannot move from ' . $return->status . ' to ' . $data['status'] . '.');
        }

        $return->update([
            'status'          => $data['status'],
            'resolution_note' => $data['note'] ?? $return->resolution_note,
            'handled_by'      => auth()->id(),
            'handled_at'      => now(),
        ]);

        return redirect()->route('admin.returns.show', $return->id)->with('success', 'Return status updated.');
    }

    /* ---------------- Claims ---------------- */

    public function claimsIndex()
    {
        return view('admin.returns.claims', [
            'statusMap' => config('admin_quotes.claim_statuses'),
            'types'     => config('admin_quotes.claim_types'),
        ]);
    }

    public function claimsData(Request $request)
    {
        $query = Claim::query()->with('user:id,name,email');

        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('description', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $query->orderByDesc('created_at');
        $map = config('admin_quotes.claim_statuses');

        return response()->json(
            $query->paginate(dp_per_page($request))->through(function (Claim $c) use ($map) {
                $meta = $map[$c->status] ?? ['?', 'bg-secondary'];
                return [
                    'id'         => $c->id,
                    'user'       => optional($c->user)->name ?: '—',
                    'email'      => optional($c->user)->email,
                    'type'       => config('admin_quotes.claim_types.' . $c->type, $c->type),
                    'amount'     => number_format((float) $c->amount_claimed, 2),
                    'payout'     => $c->payout !== null ? number_format((float) $c->payout, 2) : null,
                    'status'     => $c->status,
                    'status_lbl' => $meta['label'],
                    'color'      => $meta['color'],
                    'created_at' => optional($c->created_at)->format('M d, Y'),
                    'urls'       => [
                        'show' => route('admin.returns.claims-show', $c->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function claimShow(Claim $claim)
    {
        $claim->load('user:id,name,email');

        return view('admin.returns.claim-show', [
            'claim'     => $claim,
            'statusMap' => config('admin_quotes.claim_statuses'),
        ]);
    }

    public function claimUpdate(Request $request, Claim $claim)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(config('admin_quotes.claim_statuses'))),
            'payout' => 'nullable|numeric|min:0',
        ]);

        $allowed = config('admin_quotes.claim_statuses.' . $claim->status . '.next', []);
        if (!in_array($data['status'], $allowed, true)) {
            return redirect()->route('admin.returns.claims-show', $claim->id)
                ->with('error', 'Invalid status transition.');
        }

        $claim->update([
            'status'    => $data['status'],
            'payout'    => $data['payout'] ?? $claim->payout,
            'closed_at' => in_array($data['status'], ['settled', 'denied'], true) ? now() : null,
        ]);

        return redirect()->route('admin.returns.claims-show', $claim->id)->with('success', 'Claim updated.');
    }
}
