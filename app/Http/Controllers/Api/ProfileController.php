<?php
/* mobile-api-v5 — upload-verification marker (2026-08-28) */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /** Update basic profile fields (name + phone) — any authenticated role. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'number' => 'sometimes|string|max:30',
        ]);

        if (empty($data)) {
            return response()->json(['message' => 'Nothing to update.'], 422);
        }

        $user = $request->user();
        $user->update(array_filter($data, fn($v) => $v !== null));

        return response()->json([
            'message' => 'Profile updated.',
            'user'    => ['id' => $user->id, 'name' => $user->name, 'number' => $user->number],
        ]);
    }

    /** Change password — requires the current one. */
    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        return response()->json(['message' => 'Password changed.']);
    }
}
