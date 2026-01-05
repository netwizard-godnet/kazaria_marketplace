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

    public string $version = '';

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
                'image_url' => $popup->image_url,
                'width' => $popup->width ?? 300,
                'height' => $popup->height ?? 300,
                'layout' => $popup->layout ?? 'stacked',
                'delay' => $popup->delay_seconds,
                'frequency' => $popup->frequency,
                'max_impressions' => $popup->max_impressions,
                'priority' => $popup->priority,
            ];
        }, $this->popups);

        // Générer un hash basé sur le contenu des popups pour le cache busting
        // Ce hash change uniquement quand les popups changent
        // On inclut aussi un timestamp de la dernière modification pour forcer le rechargement
        $popupsCollection = collect($this->popups);
        $lastModified = $popupsCollection->max(function ($popup) {
            return $popup->updated_at?->timestamp ?? 0;
        }) ?? time();
        $payloadHash = md5(json_encode($this->payload) . $lastModified);
        $this->version = substr($payloadHash, 0, 8);

        // Générer un hash basé sur le contenu des popups pour le cache busting
        // Ce hash change uniquement quand les popups changent
        // On inclut aussi un timestamp de la dernière modification pour forcer le rechargement
        $popupsCollection = collect($this->popups);
        $lastModified = $popupsCollection->max('updated_at')?->timestamp ?? time();
        $payloadHash = md5(json_encode($this->payload) . $lastModified);
        $this->version = substr($payloadHash, 0, 8);
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
            'version' => $this->version,
        ]);
    }
}
