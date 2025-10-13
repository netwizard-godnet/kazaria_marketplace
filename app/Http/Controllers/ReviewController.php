<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewVote;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Obtenir les avis d'un produit
     */
    public function getProductReviews($productId, Request $request)
    {
        $sortBy = $request->get('sort', 'recent'); // recent, helpful, rating_high, rating_low
        $perPage = $request->get('per_page', 10);

        $query = Review::where('product_id', $productId)
            ->approved()
            ->with(['user', 'order']);

        // Tri
        switch ($sortBy) {
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            case 'recent':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $reviews = $query->paginate($perPage);

        // Statistiques
        $stats = [
            'average_rating' => (float) Review::getAverageRating($productId),
            'total_reviews' => (int) Review::getTotalReviews($productId),
            'distribution' => Review::getRatingDistribution($productId),
        ];

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    /**
     * Ajouter un avis
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour laisser un avis'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si l'utilisateur a déjà laissé un avis
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà laissé un avis pour ce produit'
            ], 422);
        }

        // Vérifier si l'utilisateur a acheté le produit
        $hasOrdered = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', function($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->first();

        try {
            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'order_id' => $hasOrdered ? $hasOrdered->id : null,
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_verified_purchase' => $hasOrdered ? true : false,
                'is_approved' => true, // Auto-approuvé (peut être modifié pour modération)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Votre avis a été publié avec succès',
                'review' => $review->load('user')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de l\'avis'
            ], 500);
        }
    }

    /**
     * Voter pour un avis (utile/pas utile)
     */
    public function vote(Request $request, $reviewId)
    {
        $validator = Validator::make($request->all(), [
            'is_helpful' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation'
            ], 422);
        }

        $user = $request->user();
        $sessionId = $request->header('X-Session-ID');

        // Vérifier si déjà voté
        $existingVote = ReviewVote::where('review_id', $reviewId)
            ->where(function($query) use ($user, $sessionId) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if ($existingVote) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà voté pour cet avis'
            ], 422);
        }

        try {
            ReviewVote::create([
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId,
                'review_id' => $reviewId,
                'is_helpful' => $request->is_helpful,
            ]);

            // Mettre à jour le compteur
            if ($request->is_helpful) {
                $review = Review::find($reviewId);
                $review->increment('helpful_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Merci pour votre retour'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vote'
            ], 500);
        }
    }

}
