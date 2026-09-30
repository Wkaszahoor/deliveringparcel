<?php

namespace App\Http\Controllers\Admin\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\WarehouseBin;
use App\Models\WarehousePackage;
use App\Models\WarehouseShipment;
use App\Models\WarehouseShipmentEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    /* ================= Bins ================= */

    public function binsIndex()
    {
        return view('admin.warehouse.bins.index');
    }

    public function binsData(Request $request)
    {
        $query = WarehouseBin::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('code', 'like', $like)->orWhere('zone', 'like', $like);
            });
        }
        $query->orderBy('code');

        return response()->json($query->paginate(dp_per_page($request))->through(function (WarehouseBin $b) {
            $count = $b->packages()->where('status', 'received')->count();
            return [
                'id'     => $b->id,
                'code'   => $b->code,
                'zone'   => $b->zone ?: '—',
                'occ'    => $count . ' / ' . $b->capacity,
                'occ_pct' => $b->capacity ? min(100, (int) round(100 * $count / $b->capacity)) : 0,
                'active' => (bool) $b->is_active,
                'urls'   => [
                    'edit'   => route('admin.warehouse.bins.edit', $b->id),
                    'delete' => route('admin.warehouse.bins.destroy', $b->id),
                ],
            ];
        })->toArray());
    }

    public function binsCreate()
    {
        return view('admin.warehouse.bins.form', ['bin' => new WarehouseBin(['capacity' => 100, 'is_active' => true])]);
    }

    public function binsStore(Request $request)
    {
        WarehouseBin::create($this->binRules($request));

        return redirect()->route('admin.warehouse.bins.index')->with('success', 'Bin created.');
    }

    public function binsEdit(WarehouseBin $bin)
    {
        return view('admin.warehouse.bins.form', ['bin' => $bin]);
    }

    public function binsUpdate(Request $request, WarehouseBin $bin)
    {
        $bin->update($this->binRules($request, $bin->id));

        return redirect()->route('admin.warehouse.bins.index')->with('success', 'Bin updated.');
    }

    public function binsDestroy(WarehouseBin $bin)
    {
        $bin->delete();

        return redirect()->route('admin.warehouse.bins.index')->with('success', 'Bin deleted.');
    }

    private function binRules(Request $request, $ignoreId = null)
    {
        $data = $request->validate([
            'code'     => 'required|string|max:30|unique:warehouse_bins,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'zone'     => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:100000',
            'notes'    => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');

        return $data;
    }

    /* ================= Packages (inbound) ================= */

    public function packagesIndex()
    {
        return view('admin.warehouse.packages.index', ['statusMap' => config('admin_warehouse.package_statuses')]);
    }

    public function packagesData(Request $request)
    {
        $query = WarehousePackage::query()->with('user:id,name,email', 'bin:id,code');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('expected_tracking', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $query->orderByDesc('id');
        $map = config('admin_warehouse.package_statuses');

        return response()->json($query->paginate(dp_per_page($request))->through(function (WarehousePackage $p) use ($map) {
            $m = $map[$p->status] ?? ['label' => $p->status, 'color' => 'bg-secondary'];
            return [
                'id'       => $p->id,
                'user'     => optional($p->user)->name ?: '—',
                'email'    => optional($p->user)->email,
                'tracking' => $p->expected_tracking ?: '—',
                'bin'      => optional($p->bin)->code ?: '—',
                'status'   => $m['label'],
                'color'    => $m['color'],
                'received' => optional($p->received_at)->format('M d, Y') ?: '—',
                'urls'     => ['receive' => route('admin.warehouse.packages.receive', $p->id)],
            ];
        })->toArray());
    }

    public function packagesCreate()
    {
        return view('admin.warehouse.packages.receive', [
            'package' => new WarehousePackage(['status' => 'pending']),
            'bins'    => WarehouseBin::where('is_active', true)->orderBy('code')->get(['id', 'code', 'zone']),
        ]);
    }

    /** Register an inbound package (optionally immediately receive it). */
    public function packagesStore(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'nullable|integer|exists:users,id',
            'expected_tracking' => 'nullable|string|max:100',
            'storage_bin_id'   => 'nullable|integer|exists:warehouse_bins,id',
            'status'           => 'nullable|in:pending,received,damaged,returned',
            'notes'            => 'nullable|string|max:2000',
            'photos.*'         => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            $dir = public_path('../uploads/warehouse');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '-' . Str::lower(Str::random(6)) . '.' . $file->getClientOriginalExtension();
                    if (preg_match('/\.(php\d?|phtml|phar)$/i', $name)) {
                        continue;
                    }
                    $file->move($dir, $name);
                    $photos[] = 'uploads/warehouse/' . $name;
                }
            }
        }

        $receive = ($data['status'] ?? 'pending') === 'received';
        WarehousePackage::create([
            'user_id'          => $data['user_id'] ?? null,
            'expected_tracking' => $data['expected_tracking'] ?? null,
            'storage_bin_id'   => $receive ? ($data['storage_bin_id'] ?? null) : null,
            'status'           => $data['status'] ?? 'pending',
            'received_at'      => $receive ? now() : null,
            'notes'            => $data['notes'] ?? null,
            'photos'           => $photos,
            'created_by'       => auth()->id(),
        ]);

        return redirect()->route('admin.warehouse.packages.index')->with('success', 'Package registered.');
    }

    /** Receive an existing pending package: assign bin + condition. */
    public function packagesReceive(Request $request, WarehousePackage $package)
    {
        $data = $request->validate([
            'storage_bin_id' => 'required|integer|exists:warehouse_bins,id',
            'status'         => 'required|in:received,damaged,returned',
            'notes'          => 'nullable|string|max:2000',
        ]);
        $package->update([
            'storage_bin_id' => $data['storage_bin_id'],
            'status'         => $data['status'],
            'received_at'    => now(),
            'notes'          => $data['notes'] ?? $package->notes,
        ]);

        return redirect()->route('admin.warehouse.packages.index')->with('success', 'Package received into bin.');
    }

    public function packagesReceiveForm(WarehousePackage $package)
    {
        return view('admin.warehouse.packages.receive', [
            'package' => $package,
            'bins'    => WarehouseBin::where('is_active', true)->orderBy('code')->get(['id', 'code', 'zone']),
        ]);
    }

    /* ================= Consolidation + shipments ================= */

    public function shipmentsIndex()
    {
        return view('admin.warehouse.shipments.index', ['statusMap' => config('admin_warehouse.shipment_statuses')]);
    }

    public function shipmentsData(Request $request)
    {
        $query = WarehouseShipment::query()->with('user:id,name,email')->withCount('packages');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('code', 'like', $like)->orWhere('tracking_number', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $query->orderByDesc('id');
        $map = config('admin_warehouse.shipment_statuses');

        return response()->json($query->paginate(dp_per_page($request))->through(function (WarehouseShipment $s) use ($map) {
            $m = $map[$s->status] ?? ['label' => $s->status, 'color' => 'bg-secondary'];
            return [
                'id'       => $s->id,
                'code'     => $s->code,
                'user'     => optional($s->user)->name ?: '—',
                'carrier'  => $s->carrier_name ?: '—',
                'tracking' => $s->tracking_number ?: '—',
                'packages' => $s->packages_count,
                'status'   => $m['label'],
                'color'    => $m['color'],
                'at'       => optional($s->created_at)->format('M d, Y'),
                'urls'     => ['show' => route('admin.warehouse.shipments.show', $s->id)],
            ];
        })->toArray());
    }

    /** Consolidate selected received packages of one user into a new shipment. */
    public function consolidate(Request $request)
    {
        $data = $request->validate([
            'package_ids'   => 'required|array|min:1',
            'package_ids.*' => 'integer|exists:warehouse_packages,id',
            'carrier_name'  => 'nullable|string|max:100',
        ]);

        $packages = WarehousePackage::whereIn('id', $data['package_ids'])->where('status', 'received')->get();
        abort_if($packages->isEmpty(), 422, 'No received packages selected.');
        $userId = $packages->first()->user_id;
        abort_if($packages->pluck('user_id')->unique()->count() > 1, 422, 'Packages must belong to one customer.');

        $shipment = WarehouseShipment::create([
            'code'         => 'WHS-' . strtoupper(Str::random(8)),
            'user_id'      => $userId,
            'carrier_name' => $data['carrier_name'] ?? null,
            'status'       => 'preparing',
        ]);
        $shipment->packages()->sync($packages->pluck('id'));
        WarehouseShipmentEvent::create(['shipment_id' => $shipment->id, 'status' => 'preparing', 'note' => 'Consolidated ' . $packages->count() . ' package(s).']);

        return redirect()->route('admin.warehouse.shipments.show', $shipment->id)->with('success', 'Shipment ' . $shipment->code . ' created.');
    }

    public function shipmentsShow(WarehouseShipment $shipment)
    {
        $shipment->load('packages.bin:id,code', 'user:id,name,email', 'events');

        return view('admin.warehouse.shipments.show', [
            'shipment'  => $shipment,
            'statusMap' => config('admin_warehouse.shipment_statuses'),
        ]);
    }

    public function shipmentsUpdate(Request $request, WarehouseShipment $shipment)
    {
        $data = $request->validate([
            'status'         => 'required|in:' . implode(',', array_keys(config('admin_warehouse.shipment_statuses'))),
            'carrier_name'   => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $allowed = config('admin_warehouse.shipment_statuses.' . $shipment->status . '.next', []);
        if (!in_array($data['status'], $allowed, true)) {
            return redirect()->route('admin.warehouse.shipments.show', $shipment->id)
                ->with('error', 'Cannot move from ' . $shipment->status . ' to ' . $data['status'] . '.');
        }

        $shipment->update([
            'status'          => $data['status'],
            'carrier_name'    => $data['carrier_name'] ?? $shipment->carrier_name,
            'tracking_number' => $data['tracking_number'] ?? $shipment->tracking_number,
            'dispatched_at'   => $data['status'] === 'dispatched' ? now() : $shipment->dispatched_at,
            'delivered_at'    => $data['status'] === 'delivered' ? now() : $shipment->delivered_at,
        ]);
        WarehouseShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'status'      => $data['status'],
            'note'        => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.warehouse.shipments.show', $shipment->id)->with('success', 'Shipment updated.');
    }

    /** Printable dispatch manifest for a shipment. */
    public function manifest(WarehouseShipment $shipment)
    {
        $shipment->load('packages.user:id,name', 'user:id,name,email');

        return view('admin.warehouse.manifest', ['shipment' => $shipment]);
    }
}
