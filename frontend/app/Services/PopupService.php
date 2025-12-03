<?php

namespace App\Services;

use App\Models\Popup;
use Illuminate\Http\Request;

class PopupService
{
    public function getActivePopups(Request $request): array
    {
        $path = '/' . ltrim($request->path(), '/');
        $device = $this->resolveDevice($request->userAgent());

        return Popup::active()
            ->orderByDesc('priority')
            ->get()
            ->filter(function (Popup $popup) use ($path, $device) {
                if (!empty($popup->display_devices) && !in_array($device, $popup->display_devices, true)) {
                    return false;
                }

                if (empty($popup->display_pages)) {
                    return true;
                }

                foreach ($popup->display_pages as $rule) {
                    $rule = trim($rule);

                    if ($rule === 'home' && $path === '/') {
                        return true;
                    }

                    if ($rule === 'category' && str_contains($path, '/categorie')) {
                        return true;
                    }

                    if ($rule === 'product' && str_contains($path, '/produit')) {
                        return true;
                    }

                    if ($rule === 'cart' && str_contains($path, '/panier')) {
                        return true;
                    }

                    if ($rule === 'checkout' && str_contains($path, '/checkout')) {
                        return true;
                    }

                    if ($rule && str_contains($path, $rule)) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
    }

    protected function resolveDevice(?string $userAgent): string
    {
        $ua = strtolower($userAgent ?? '');

        if (str_contains($ua, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }
}

