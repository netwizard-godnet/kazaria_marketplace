@php
use App\Models\Banner;

// Récupérer les bannières actives pour la sidebar (à côté du carousel)
$banners = Banner::where('placement', 'sidebar')
    ->where('is_active', true)
    ->where(function($query) {
        $query->whereNull('starts_at')
              ->orWhere('starts_at', '<=', now());
    })
    ->where(function($query) {
        $query->whereNull('ends_at')
              ->orWhere('ends_at', '>=', now());
    })
    ->orderBy('sort_order')
    ->take(2) // Limiter à 2 bannières pour la sidebar
    ->get();
@endphp

@if($banners->count() > 0)
    @foreach($banners as $banner)
    <div class="col-md-12 mb-3">
        <a href="{{ $banner->link_url ?: '#' }}" class="d-block text-decoration-none">
            <div class="banner-card position-relative overflow-hidden rounded" style="height: 200px;">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-100 h-100" style="object-fit: cover;">
                @if($banner->title || $banner->subtitle)
                <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                    @if($banner->title)
                    <h6 class="text-white mb-1 fw-bold">{{ $banner->title }}</h6>
                    @endif
                    @if($banner->subtitle)
                    <small class="text-white">{{ $banner->subtitle }}</small>
                    @endif
                </div>
                @endif
            </div>
        </a>
    </div>
    @endforeach
@else
    <!-- Fallback si pas de bannières -->
    <div class="col-md-12 mb-3">
        <div style="background: url('{{ asset('images/bg-2.jpg') }}'); background-size: cover; background-repeat: no-repeat; height: 200px; border-radius: 8px;"></div>
    </div>
    <div class="col-md-12 mb-3">
        <div style="background: url('{{ asset('images/bg-2.jpg') }}'); background-size: cover; background-repeat: no-repeat; height: 200px; border-radius: 8px;"></div>
    </div>
@endif

<style>
.banner-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.banner-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.banner-card img {
    transition: transform 0.3s ease;
}

.banner-card:hover img {
    transform: scale(1.05);
}
</style>
