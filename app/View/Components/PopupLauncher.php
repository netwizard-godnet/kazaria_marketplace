<?php

namespace App\View\Components;

use App\Services\PopupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Illuminate\Support\Str;

class PopupLauncher extends Component
{
    /**
     * @var array<int, \App\Models\Popup>
     */
    public array $popups = [];
    public array $payload = [];

    public function __construct(Request $request, PopupService $popupService)
    {
        $this->popups = $popupService->getActivePopups($request);
        $this->payload = array_map(function ($popup) {
            $image = $popup->image;

            if ($image) {
                $trimmed = ltrim($image, '/');
                if (!Str::startsWith($trimmed, ['http://', 'https://'])) {
                    $image = asset('storage/' . $trimmed);
                }
            }

            return [
                'id' => $popup->id,
                'slug' => $popup->slug,
                'title' => $popup->title,
                'content' => $popup->content,
                'cta_text' => $popup->cta_text,
                'cta_url' => $popup->cta_url,
                'image' => $image,
                'layout' => $popup->layout ?? 'left-right',
                'delay' => $popup->delay_seconds,
                'frequency' => $popup->frequency,
                'max_impressions' => $popup->max_impressions,
                'priority' => $popup->priority,
            ];
        }, $this->popups);
    }

    public function shouldRender(): bool
    {
        return !empty($this->popups);
    }

    public function render(): View
    {
        return view('components.popup-launcher', [
            'popups' => $this->popups,
            'payload' => $this->payload,
        ]);
    }
}
