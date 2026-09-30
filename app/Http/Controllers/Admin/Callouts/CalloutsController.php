<?php

namespace App\Http\Controllers\Admin\Callouts;

use App\Http\Controllers\Controller;
use App\Models\OrderCallout;
use Illuminate\Http\Request;

/**
 * Admin CRUD for the order-status callout cards shown to customers on the
 * order pages (legacy /orders/{id} now, new design keyed by raw order_status).
 * Admin → Content → Order Callouts.
 */
class CalloutsController extends Controller
{
    public function index()
    {
        $callouts = OrderCallout::query()
            ->orderBy('status_key')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('status_key');

        return view('admin.callouts.index', compact('callouts'));
    }

    public function create()
    {
        return view('admin.callouts.create', ['callout' => new OrderCallout([
            'style' => 'info',
            'sort_order' => 10,
            'is_enabled' => true,
        ])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        OrderCallout::create($data + ['is_enabled' => $request->has('is_enabled')]);

        return redirect()->route('admin.callouts.index')
            ->with('success', 'Callout created.');
    }

    public function edit(OrderCallout $callout)
    {
        return view('admin.callouts.edit', compact('callout'));
    }

    public function update(Request $request, OrderCallout $callout)
    {
        $data = $this->validated($request);

        $callout->update($data + ['is_enabled' => $request->has('is_enabled')]);

        return redirect()->route('admin.callouts.index')
            ->with('success', 'Callout updated.');
    }

    public function toggle(OrderCallout $callout)
    {
        $callout->update(['is_enabled' => !$callout->is_enabled]);

        return back()->with('success', $callout->is_enabled ? 'Callout enabled.' : 'Callout disabled.');
    }

    public function destroy(OrderCallout $callout)
    {
        $callout->delete();

        return redirect()->route('admin.callouts.index')
            ->with('success', 'Callout deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'status_key' => [
                'required', 'string', 'max:100',
                // known legacy slot OR a slug-style custom key (raw status)
                function ($attribute, $value, $fail) {
                    $ok = array_key_exists($value, OrderCallout::SLOTS)
                        || preg_match('/^[a-z0-9_\- ]+$/i', $value);
                    if (!$ok) {
                        $fail('The status key must be a known slot or contain only letters, numbers, spaces, dashes and underscores.');
                    }
                },
            ],
            'style'      => 'required|in:' . implode(',', OrderCallout::STYLES),
            'heading'    => 'nullable|string|max:255',
            'body'       => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
    }
}
