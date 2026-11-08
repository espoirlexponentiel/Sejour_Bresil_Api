<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Stay;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Get dashboard key metrics and analytics.
     */
    public function index(): JsonResponse
    {
        $totalStays = Stay::count();
        $activeStays = Stay::where('status', 'active')->count();

        $totalBookings = Booking::count();
        $confirmedBookings = Booking::where('status', 'confirmée')->count();
        $pendingBookings = Booking::where('status', 'en_attente')->count();
        $cancelledBookings = Booking::where('status', 'annulée')->count();

        $totalRevenue = (float) Booking::where('status', 'confirmée')->sum('total_price');
        $potentialRevenue = (float) Booking::where('status', 'en_attente')->sum('total_price');
        $totalTravelers = (int) Booking::where('status', 'confirmée')->sum('travelers');

        $recentBookings = Booking::with('stay')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_stays' => $totalStays,
                    'active_stays' => $activeStays,
                    'total_bookings' => $totalBookings,
                    'confirmed_bookings' => $confirmedBookings,
                    'pending_bookings' => $pendingBookings,
                    'cancelled_bookings' => $cancelledBookings,
                    'total_revenue' => $totalRevenue,
                    'potential_revenue' => $potentialRevenue,
                    'total_travelers' => $totalTravelers,
                ],
                'recent_bookings' => $recentBookings,
            ],
        ]);
    }
}

