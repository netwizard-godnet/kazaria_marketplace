@include('layouts.header')

    <!-- Container global pour les alertes Bootstrap -->
    <div id="alertContainer" class="position-fixed top-0 start-50 translate-middle-x mt-3 z-index-9x" style="z-index: 11000; min-width: 400px; max-width: 600px;"></div>

    {{-- Ici viendra le contenu de la page --}}
    @yield('content')

@include('layouts.footer')
