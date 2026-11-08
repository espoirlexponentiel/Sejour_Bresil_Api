<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Stay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with('stay');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('stay_title', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $bookings->count(),
            'data' => $bookings,
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stay_id' => 'nullable|exists:stays,id',
            'stay_title' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'travelers' => 'required|integer|min:1|max:20',
            'travel_date' => 'required|string|max:100',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Auto-calculate total price if stay_id provided and total_price is missing
        if ((!isset($validated['total_price']) || $validated['total_price'] <= 0) && !empty($validated['stay_id'])) {
            $stay = Stay::find($validated['stay_id']);
            if ($stay) {
                $validated['total_price'] = $stay->price * $validated['travelers'];
            }
        }

        $validated['reference'] = 'RES-' . strtoupper(Str::random(6));
        $validated['status'] = 'en_attente';

        $booking = Booking::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Demande de réservation enregistrée avec succès',
            'data' => $booking->load('stay'),
        ], 201);
    }

    /**
     * Display the specified booking.
     */
    public function show(int $id): JsonResponse
    {
        $booking = Booking::with('stay')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation introuvable',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * Update the specified booking status or details.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation introuvable',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|required|in:en_attente,confirmée,annulée',
            'notes' => 'nullable|string',
            'travel_date' => 'sometimes|string',
            'travelers' => 'sometimes|integer|min:1',
            'total_price' => 'sometimes|numeric|min:0',
        ]);

        $booking->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Réservation mise à jour avec succès',
            'data' => $booking->fresh()->load('stay'),
        ]);
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(int $id): JsonResponse
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation introuvable',
            ], 404);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Réservation supprimée avec succès',
        ]);
    }
}

