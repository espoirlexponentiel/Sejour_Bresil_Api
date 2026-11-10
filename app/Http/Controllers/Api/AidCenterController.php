<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AidCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AidCenterController extends Controller
{
    /**
     * Display a listing of aid centers, optionally filtered by city_slug.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AidCenter::query();

        if ($request->filled('city_slug') && $request->city_slug !== 'all') {
            $query->where('city_slug', $request->city_slug);
        }

        $centers = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => $centers->count(),
            'data' => $centers,
        ]);
    }

    /**
     * Store a newly created aid center (Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type_badge' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'city_slug' => 'required|string|max:100',
            'services' => 'nullable|array',
            'address' => 'required|string|max:255',
            'hours' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string',
        ]);

        $center = AidCenter::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Centre d\'accueil créé avec succès',
            'data' => $center,
        ], 201);
    }

    /**
     * Update an aid center (Admin).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $center = AidCenter::find($id);

        if (!$center) {
            return response()->json(['success' => false, 'message' => 'Centre introuvable'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type_badge' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'city_slug' => 'sometimes|required|string|max:100',
            'services' => 'nullable|array',
            'address' => 'sometimes|required|string|max:255',
            'hours' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string',
        ]);

        $center->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Centre mis à jour avec succès',
            'data' => $center->fresh(),
        ]);
    }

    /**
     * Delete an aid center (Admin).
     */
    public function destroy(int $id): JsonResponse
    {
        $center = AidCenter::find($id);

        if (!$center) {
            return response()->json(['success' => false, 'message' => 'Centre introuvable'], 404);
        }

        $center->delete();

        return response()->json([
            'success' => true,
            'message' => 'Centre supprimé avec succès',
        ]);
    }
}

