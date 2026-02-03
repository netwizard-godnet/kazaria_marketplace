<?php

namespace App\Http\Controllers;

use App\Models\Popup;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PopupController extends Controller
{
    /**
     * Get active popups for mobile app
     * Filters by:
     * - Active status
     * - Display date range
     * - Device (mobile)
     */
    public function getActivePopups(Request $request)
    {
        try {
            $now = Carbon::now();
            
            // Get active popups that should display on mobile devices
            $popups = Popup::where('is_active', true)
                ->where(function ($query) use ($now) {
                    // Filter by date range
                    $query->where(function ($q) use ($now) {
                        // No start date, or started
                        $q->whereNull('display_start')
                            ->orWhere('display_start', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        // No end date, or not ended
                        $q->whereNull('display_end')
                            ->orWhere('display_end', '>=', $now);
                    });
                })
                ->where(function ($query) {
                    // Filter by device - must include 'mobile' or be empty (all devices)
                    $query->whereNull('display_devices')
                        ->orWhereRaw("JSON_CONTAINS(display_devices, '\"mobile\"')")
                        ->orWhereRaw("JSON_CONTAINS(display_devices, '\"app\"')");
                })
                ->orderByDesc('priority')
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($popup) {
                    return [
                        'id' => $popup->id,
                        'title' => $popup->title,
                        'content' => $popup->content,
                        'cta_text' => $popup->cta_text,
                        'cta_url' => $popup->cta_url,
                        'image' => $popup->image ? url('storage/' . $popup->image) : null,
                        'image_url' => $popup->image_url,
                        'width' => $popup->width ?? 300,
                        'height' => $popup->height ?? 300,
                        'layout' => $popup->layout ?? 'stacked',
                        'frequency' => $popup->frequency ?? 'once_per_session',
                        'delay_seconds' => $popup->delay_seconds ?? 0,
                        'slug' => $popup->slug,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $popups,
                'count' => $popups->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Popup retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des popups',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track popup impression
     */
    public function trackImpression(Request $request, $popupId)
    {
        try {
            $popup = Popup::find($popupId);
            
            if (!$popup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Popup not found',
                ], 404);
            }

            // Increment impression counter if tracking is enabled
            if ($popup->options && isset($popup->options['track_impressions'])) {
                // You can implement impression tracking here
                // For now, just return success
            }

            return response()->json([
                'success' => true,
                'message' => 'Impression tracked',
            ]);
        } catch (\Exception $e) {
            \Log::error('Impression tracking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error tracking impression',
            ], 500);
        }
    }

    /**
     * Track popup click/conversion
     */
    public function trackClick(Request $request, $popupId)
    {
        try {
            $popup = Popup::find($popupId);
            
            if (!$popup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Popup not found',
                ], 404);
            }

            // Track click if enabled
            if ($popup->options && isset($popup->options['track_clicks'])) {
                // You can implement click tracking here
            }

            return response()->json([
                'success' => true,
                'message' => 'Click tracked',
            ]);
        } catch (\Exception $e) {
            \Log::error('Click tracking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error tracking click',
            ], 500);
        }
    }
}
