<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderByDesc('created_at')->paginate(20);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:500',
            'discount_percent' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:10',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $codes = [];
        for ($i = 0; $i < (int)$validated['quantity']; $i++) {
            do {
                $code = strtoupper(($validated['prefix'] ?? 'KAZ') . '-' . Str::random(6));
            } while (Coupon::where('code', $code)->exists());

            $codes[] = Coupon::create([
                'code' => $code,
                'discount_percent' => (int)$validated['discount_percent'],
                'max_uses' => $validated['max_uses'] ?? null,
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->back()->with('success', count($codes) . ' code(s) promo créés.');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();
        return redirect()->back()->with('success', "Code {$coupon->code} " . ($coupon->is_active ? 'activé' : 'désactivé'));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->back()->with('success', 'Code supprimé');
    }
}


