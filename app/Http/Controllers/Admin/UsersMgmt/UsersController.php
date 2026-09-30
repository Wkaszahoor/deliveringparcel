<?php

namespace App\Http\Controllers\Admin\UsersMgmt;

use App\Http\Controllers\Admin\OrdersMgmt\OrderStatusService;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Users Management (new module — /admin/users*).
 *
 * Reads the live `users` (19k rows) / `users_roles` / `roles` tables.
 * Rules enforced here:
 *   - Only name / phone (number) / status are editable.
 *   - `status` is NULL-safe: NULL and 'active' both mean Active (live rows are NULL).
 *   - An admin can never block their own account (auth()->id()).
 *   - Role assignment is never touched (no removing Admin from self or anyone).
 *   - No delete endpoint exists for users on purpose (live prod copy).
 */
class UsersController extends Controller
{
    /** Account statuses editable in the UI. NULL in DB == 'active'. */
    public const STATUSES = [
        'active'  => 'Active',
        'blocked' => 'Blocked',
    ];

    /**
     * GET /admin/users — list shell (rows load via /admin/users/data).
     */
    public function index()
    {
        return view('admin.usersm.index', [
            'stats'        => $this->stats(),
            'statusOptions' => self::STATUSES,
            'roleOptions'  => ['admin' => 'Admins', 'client' => 'Clients'],
        ]);
    }

    /**
     * GET /admin/users/data — paginated JSON for DP.infiniteScroll.
     * Supports: ?page= ?q= (name/email/phone) ?role= (roles.slug via
     * users_roles join) ?status= ('active'|'blocked') ?sort=
     */
    public function data(Request $request)
    {
        $query = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.number',
                'users.type',
                'users.avatar',
                'users.status',
                'users.created_at',
            ])
            ->selectRaw('(SELECT COUNT(*) FROM orders o WHERE o.user_id = users.id) AS orders_count')
            ->selectRaw('(SELECT GROUP_CONCAT(r.slug) FROM users_roles ur LEFT JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = users.id) AS roles_csv');

        // --- search: name / email / phone
        if ($q = trim((string) $request->query('q', ''))) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('users.number', 'like', $like);
            });
        }

        // --- role filter (users_roles ⋈ roles, via EXISTS to avoid row duplication)
        $role = (string) $request->query('role', '');
        if ($role !== '') {
            $query->whereExists(function ($w) use ($role) {
                $w->selectRaw(1)
                    ->from('users_roles AS ur')
                    ->join('roles AS r', 'r.id', '=', 'ur.role_id')
                    ->whereColumn('ur.user_id', 'users.id')
                    ->where('r.slug', $role);
            });
        }

        // --- status filter (NULL = active)
        $status = (string) $request->query('status', '');
        if ($status !== '') {
            if (!array_key_exists($status, self::STATUSES)) {
                $status = '';
            }
        }
        if ($status === 'active') {
            $query->where(function ($w) {
                $w->whereNull('users.status')->orWhere('users.status', 'active');
            });
        } elseif ($status === 'blocked') {
            $query->where('users.status', 'blocked');
        }

        // --- sorting (whitelist; exactly one branch always applies)
        switch ((string) $request->query('sort', 'newest')) {
            case 'oldest':
                $query->orderBy('users.created_at', 'asc')->orderBy('users.id', 'asc');
                break;
            case 'name':
                $query->orderBy('users.name', 'asc');
                break;
            case 'orders_desc':
                $query->orderByRaw('(SELECT COUNT(*) FROM orders o WHERE o.user_id = users.id) DESC, users.id DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('users.created_at', 'desc')->orderBy('users.id', 'desc');
                break;
        }

        return response()->json($query->paginate(20)->toArray());
    }

    /**
     * GET /admin/users/{id} — profile + roles + order-history endpoint info.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        $ordersCount = Orders::where('user_id', $user->id)->count();
        $totalSpent  = (float) Orders::where('user_id', $user->id)
            ->where('order_status', 'completed')
            ->sum(DB::raw('CAST(total AS UNSIGNED)'));

        return view('admin.usersm.show', [
            'user'         => $user,
            'roles'        => $user->roles()->orderBy('id')->get(),
            'ordersCount'  => $ordersCount,
            'totalSpent'   => $totalSpent,
            'statusOptions' => self::STATUSES,
            'statusMap'    => OrderStatusService::all(), // badges for the lazy orders table
            'isSelf'       => $user->id === (int) auth()->id(),
        ]);
    }

    /**
     * GET /admin/users/{id}/edit — name / phone / status form.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.usersm.edit', [
            'user'          => $user,
            'statusOptions' => self::STATUSES,
            'isSelf'        => $user->id === (int) auth()->id(),
        ]);
    }

    /**
     * PUT /admin/users/{id} — update name / phone / status ONLY.
     * Never touches email, password, type or roles.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isSelf = $user->id === (int) auth()->id();

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,blocked',
        ]);

        // Protection: an admin can never block their own account.
        if ($isSelf && ($validated['status'] ?? 'active') === 'blocked') {
            return redirect()
                ->route('admin.users.edit', $user->id)
                ->with('error', 'You cannot block your own account.');
        }

        $user->update([
            'name'   => $validated['name'],
            'number' => $validated['number'] ?? '',
            'status' => ($validated['status'] ?? 'active') ?: 'active',
        ]);

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('success', "User \"{$user->name}\" updated.");
    }

    /**
     * POST /admin/users/{id}/reset-link — email a password reset link.
     */
    public function resetLink(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            $result = Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Password reset mail could not be sent (mailer error). Please try again later.',
            ], 500);
        }

        if ($result === Password::RESET_LINK_SENT) {
            return response()->json([
                'ok'      => true,
                'message' => "Password reset link emailed to {$user->email}.",
            ]);
        }

        $message = 'Reset link could not be sent.';
        if ($result === Password::RESET_THROTTLED) {
            $message = 'A reset link was already emailed recently — please wait before retrying.';
        } elseif ($result === Password::INVALID_USER) {
            $message = 'No account found with that email address.';
        }

        return response()->json(['ok' => false, 'message' => $message], 422);
    }

    /**
     * POST /admin/users/{id}/set-password — set a new password directly.
     */
    public function setPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json([
            'ok'      => true,
            'message' => "Password set for \"{$user->name}\".",
        ]);
    }

    /**
     * GET /admin/users/{id}/orders — lazy order history for the profile page.
     * Same row shape as Admin OrdersMgmt OrdersController::data().
     */
    public function userOrders(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $query = Orders::where('orders.user_id', $user->id)
            ->select([
                'orders.id',
                'orders.order_id',
                'orders.total',
                'orders.order_status',
                'orders.trackingid',
                'orders.trackinglink',
                'orders.companyname',
                'orders.archived_at',
                'orders.created_at',
            ])
            ->selectRaw('(SELECT COUNT(*) FROM orderproducts op WHERE op.order_id = orders.id) AS items_count');

        if ($q = trim((string) $request->query('q', ''))) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('orders.order_id', 'like', $like)
                    ->orWhere('orders.trackingid', 'like', $like);
            });
        }

        $query->orderBy('orders.created_at', 'desc')->orderBy('orders.id', 'desc');

        return response()->json($query->paginate(15)->toArray());
    }

    /**
     * Small KPI numbers for the index page (cheap COUNTs on live tables).
     */
    private function stats(): array
    {
        return [
            'total'   => (int) User::count(),
            'admins'  => (int) User::whereExists(function ($w) {
                $w->selectRaw(1)
                    ->from('users_roles AS ur')
                    ->join('roles AS r', 'r.id', '=', 'ur.role_id')
                    ->whereColumn('ur.user_id', 'users.id')
                    ->where('r.slug', 'admin');
            })->count(),
            'blocked' => (int) User::where('status', 'blocked')->count(),
        ];
    }
}
