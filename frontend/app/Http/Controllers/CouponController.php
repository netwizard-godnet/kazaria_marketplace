<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();
        if (!$coupon || !$coupon->isCurrentlyValid()) {
            return response()->json(['success' => false, 'message' => 'Code promo invalide ou expiré'], 422);
        }

        // Mémoriser le code promo en session pour le checkout
        session(['promo' => [
            'code' => $coupon->code,
            'percent' => $coupon->discount_percent,
        ]]);

        // Pas d’incrément d’utilisation ici, ça se fera lors de la commande
        return response()->json([
            'success' => true,
            'discount_percent' => $coupon->discount_percent,
            'code' => $coupon->code,
            'discount_amount' => round($request->subtotal * $coupon->discount_percent / 100),
        ]);
    }
}


