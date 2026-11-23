<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Admin: List all registered users with request counts and stats.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux administrateurs.'
            ], 403);
        }

        $query = User::query();

        if ($request->filled('role') && in_array($request->role, ['admin', 'user'])) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s);
            });
        }

        $users = $query->orderByDesc('id')->get();

        $userEmails = $users->pluck('email')->filter()->map(fn($e) => strtolower($e))->values()->toArray();
        $userIds = $users->pluck('id')->values()->toArray();

        $requestsByEmail = [];
        if (!empty($userEmails)) {
            $reqGroups = AssistanceRequest::whereIn('email', $userEmails)
                ->groupBy('email')
                ->selectRaw('LOWER(email) as lower_email, count(*) as count')
                ->pluck('count', 'lower_email')
                ->toArray();
            $requestsByEmail = $reqGroups;
        }

        $requestsById = [];
        if (!empty($userIds)) {
            $requestsById = AssistanceRequest::whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->selectRaw('user_id, count(*) as count')
                ->pluck('count', 'user_id')
                ->toArray();
        }

        $transformedUsers = $users->map(function ($u) use ($requestsByEmail, $requestsById) {
            $emailKey = strtolower($u->email);
            $countByEmail = $requestsByEmail[$emailKey] ?? 0;
            $countById = $requestsById[$u->id] ?? 0;
            $totalCount = max($countByEmail, $countById);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role ?? 'user',
                'requests_count' => $totalCount,
                'email_verified_at' => $u->email_verified_at ? $u->email_verified_at->toIso8601String() : null,
                'created_at' => $u->created_at ? $u->created_at->toIso8601String() : null,
                'updated_at' => $u->updated_at ? $u->updated_at->toIso8601String() : null,
            ];
        });

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'users' => User::where('role', 'user')->count(),
            'with_orders' => AssistanceRequest::distinct('email')->count('email'),
        ];

        return response()->json([
            'success' => true,
            'users' => $transformedUsers,
            'stats' => $stats,
        ]);
    }

    /**
     * Admin: Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux administrateurs.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:admin,user',
            'password' => 'nullable|string|min:6',
        ]);

        $password = !empty($validated['password']) ? $validated['password'] : Str::random(12);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($password),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Utilisateur « {$user->name} » créé avec succès.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'requests_count' => 0,
                'created_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
            ],
            'generated_password' => empty($validated['password']) ? $password : null,
        ], 201);
    }

    /**
     * Admin: Update an existing user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux administrateurs.'
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
            'password' => 'nullable|string|min:6',
        ]);

        if ($user->id === $admin->id && $validated['role'] !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas révoquer vos propres privilèges administrateur.'
            ], 400);
        }

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Utilisateur « {$user->name} » mis à jour avec succès.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Admin: Toggle role between admin and user.
     */
    public function toggleRole(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux administrateurs.'
            ], 403);
        }

        if ($admin->id === $id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier votre propre rôle.'
            ], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.'
            ], 404);
        }

        $user->role = ($user->role === 'admin') ? 'user' : 'admin';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Le rôle de « {$user->name} » a été basculé en « {$user->role} ».",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Admin: Delete user.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux administrateurs.'
            ], 403);
        }

        if ($admin->id === $id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.'
            ], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.'
            ], 404);
        }

        $name = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "Compte utilisateur « {$name} » supprimé avec succès.",
        ]);
    }
}
