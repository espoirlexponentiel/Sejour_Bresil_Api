<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demarche;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemarcheController extends Controller
{
    /**
     * Display a listing of demarches.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Demarche::query();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else if (!$request->has('status')) {
            // By default, public gets active
            $query->where('status', 'active');
        }

        $demarches = $query->orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => $demarches->count(),
            'data' => $demarches,
        ]);
    }

    /**
     * Store a newly created demarche card (Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'step_number' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'delay' => 'nullable|string|max:255',
            'cost' => 'nullable|string|max:255',
            'assistance_price' => 'nullable|numeric|min:0',
            'button_text' => 'nullable|string|max:100',
            'guide_url' => 'nullable|string',
            'official_source' => 'nullable|string',
            'questions' => 'nullable|array',
            'checklist' => 'nullable|array',
            'steps' => 'nullable|array',
            'faqs' => 'nullable|array',
            'assistance_purposes' => 'nullable|array',
            'status' => 'nullable|in:active,draft',
            'order' => 'nullable|integer',
        ]);

        $validated['step_number'] = $validated['step_number'] ?? 'ÉTAPE 0' . (Demarche::count() + 1);
        $validated['priority'] = $validated['priority'] ?? 'Priorité ' . (Demarche::count() + 1);
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['order'] = $validated['order'] ?? (Demarche::max('order') + 1);
        $validated['assistance_price'] = (array_key_exists('assistance_price', $validated) && $validated['assistance_price'] !== null)
            ? (float) $validated['assistance_price']
            : 50.00;

        $demarche = Demarche::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Carte démarche créée avec succès',
            'data' => $demarche,
        ], 201);
    }

    /**
     * Display a specific demarche.
     */
    public function show(int $id): JsonResponse
    {
        $demarche = Demarche::find($id);

        if (!$demarche) {
            return response()->json(['success' => false, 'message' => 'Démarche introuvable'], 404);
        }

        return response()->json(['success' => true, 'data' => $demarche]);
    }

    /**
     * Update a demarche card (Admin).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $demarche = Demarche::find($id);

        if (!$demarche) {
            return response()->json(['success' => false, 'message' => 'Démarche introuvable'], 404);
        }

        $validated = $request->validate([
            'step_number' => 'sometimes|string|max:50',
            'priority' => 'sometimes|string|max:50',
            'icon' => 'sometimes|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'sometimes|required|string',
            'delay' => 'nullable|string|max:255',
            'cost' => 'nullable|string|max:255',
            'assistance_price' => 'nullable|numeric|min:0',
            'button_text' => 'nullable|string|max:100',
            'guide_url' => 'nullable|string',
            'official_source' => 'nullable|string',
            'questions' => 'nullable|array',
            'checklist' => 'nullable|array',
            'steps' => 'nullable|array',
            'faqs' => 'nullable|array',
            'assistance_purposes' => 'nullable|array',
            'status' => 'sometimes|in:active,draft',
            'order' => 'sometimes|integer',
        ]);

        if (array_key_exists('assistance_price', $validated)) {
            $validated['assistance_price'] = $validated['assistance_price'] !== null
                ? (float) $validated['assistance_price']
                : 0.00;
        }

        $demarche->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Démarche mise à jour avec succès',
            'data' => $demarche->fresh(),
        ]);
    }

    /**
     * Delete a demarche card (Admin).
     */
    public function destroy(int $id): JsonResponse
    {
        $demarche = Demarche::find($id);

        if (!$demarche) {
            return response()->json(['success' => false, 'message' => 'Démarche introuvable'], 404);
        }

        $demarche->delete();

        return response()->json([
            'success' => true,
            'message' => 'Démarche supprimée avec succès',
        ]);
    }
}

