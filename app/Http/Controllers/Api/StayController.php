<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StayController extends Controller
{
    /**
     * Display a listing of stays.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Stay::query();

        // Filter by status if provided (default to active for public, or all for admin)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter featured
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        // Search in title, destination or summary
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $stays = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $stays->count(),
            'data' => $stays,
        ]);
    }

    /**
     * Store a newly created stay.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'duration' => 'required|string|max:100',
            'days' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
            'category' => 'required|string|max:100',
            'tag' => 'nullable|string|max:100',
            'featured' => 'nullable|boolean',
            'status' => 'nullable|in:active,draft,archived',
            'summary' => 'required|string',
            'highlights' => 'nullable|array',
            'included' => 'nullable|array',
            'dates' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['featured'] = $validated['featured'] ?? false;
        $validated['days'] = $validated['days'] ?? 7;

        $stay = Stay::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Séjour créé avec succès',
            'data' => $stay,
        ], 201);
    }

    /**
     * Display the specified stay by ID or slug.
     */
    public function show(string $idOrSlug): JsonResponse
    {
        $stay = is_numeric($idOrSlug)
            ? Stay::with('bookings')->find($idOrSlug)
            : Stay::with('bookings')->where('slug', $idOrSlug)->first();

        if (!$stay) {
            return response()->json([
                'success' => false,
                'message' => 'Séjour introuvable',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $stay,
        ]);
    }

    /**
     * Update the specified stay.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $stay = Stay::find($id);

        if (!$stay) {
            return response()->json([
                'success' => false,
                'message' => 'Séjour introuvable',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'destination' => 'sometimes|required|string|max:255',
            'region' => 'nullable|string|max:255',
            'duration' => 'sometimes|required|string|max:100',
            'days' => 'nullable|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
            'category' => 'sometimes|required|string|max:100',
            'tag' => 'nullable|string|max:100',
            'featured' => 'nullable|boolean',
            'status' => 'nullable|in:active,draft,archived',
            'summary' => 'sometimes|required|string',
            'highlights' => 'nullable|array',
            'included' => 'nullable|array',
            'dates' => 'nullable|array',
        ]);

        $stay->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Séjour mis à jour avec succès',
            'data' => $stay->fresh(),
        ]);
    }

    /**
     * Remove the specified stay.
     */
    public function destroy(int $id): JsonResponse
    {
        $stay = Stay::find($id);

        if (!$stay) {
            return response()->json([
                'success' => false,
                'message' => 'Séjour introuvable',
            ], 404);
        }

        $stay->delete();

        return response()->json([
            'success' => true,
            'message' => 'Séjour supprimé avec succès',
        ]);
    }
}

