<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $types = AuditLog::select('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        return view('admin.audit.index', [
            'types'     => $types,
            'entityMap' => [
                'created'  => ['Created', 'bg-success'],
                'updated'  => ['Updated', 'bg-info'],
                'deleted'  => ['Deleted', 'bg-danger'],
                'restored' => ['Restored', 'bg-warning'],
            ],
            'filters' => [
                'q'       => (string) $request->input('q', ''),
                'action'  => (string) $request->input('action', ''),
                'type'    => (string) $request->input('type', ''),
                'from'    => (string) $request->input('from', ''),
                'to'      => (string) $request->input('to', ''),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $query = AuditLog::query()->with('user:id,name,email');

        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('auditable_type', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('type')) {
            $query->where('auditable_type', $request->input('type'));
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from') . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to') . ' 23:59:59');
        }

        $query->orderByDesc('created_at');

        return response()->json(
            $query->paginate(dp_per_page($request, 20))->through(function (AuditLog $l) {
                return [
                    'id'      => $l->id,
                    'user'    => optional($l->user)->name ?: 'system',
                    'action'  => $l->action,
                    'entity'  => class_basename($l->auditable_type) . ' #' . $l->auditable_id,
                    'type'    => $l->auditable_type,
                    'old'     => $l->old_values,
                    'new'     => $l->new_values,
                    'ip'      => $l->ip,
                    'at'      => optional($l->created_at)->format('M d, Y H:i'),
                ];
            })->toArray()
        );
    }
}
