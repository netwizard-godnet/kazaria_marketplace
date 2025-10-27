<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'KAZARIA Admin Dashboard')</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon" />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts and icons -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular", 
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('kazaria-admin/assets/css/fonts.min.css') }}"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

        <!-- CSS Files -->
        <link rel="stylesheet" href="{{ asset('kazaria-admin/assets/css/bootstrap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('kazaria-admin/assets/css/plugins.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('kazaria-admin/assets/css/kaiadmin.min.css') }}" />
        
        <!-- CSS Just for demo purpose, don't include it in your project -->
        <link rel="stylesheet" href="{{ asset('kazaria-admin/assets/css/demo.css') }}" />
        
        <!-- CSS for icons -->
        <link rel="stylesheet" href="{{ asset('kazaria-admin/assets/css/fonts.min.css') }}" />
        
        <!-- Custom KAZARIA Admin CSS -->
        <link rel="stylesheet" href="{{ asset('css/admin-custom.css') }}" />
        
        <!-- Admin Header CSS -->
        <link rel="stylesheet" href="{{ asset('css/admin-header.css') }}" />
    
        <!-- Custom Admin CSS -->
    <style>
        :root {
            --kazaria-orange: #ff6b35;
            --kazaria-orange-dark: #e55a2b;
        }
        
        .sidebar[data-background-color="dark"] {
            background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        }
        
        .sidebar .nav-item.active > a {
            background-color: var(--kazaria-orange) !important;
            color: white !important;
        }
        
        .sidebar .nav-item > a:hover {
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--kazaria-orange) !important;
        }
        
        .btn-primary {
            background-color: var(--kazaria-orange);
            border-color: var(--kazaria-orange);
        }
        
        .btn-primary:hover {
            background-color: var(--kazaria-orange-dark);
            border-color: var(--kazaria-orange-dark);
        }
        
        .text-primary {
            color: var(--kazaria-orange) !important;
        }
        
        .bg-primary {
            background-color: var(--kazaria-orange) !important;
        }
    </style>
    
    @stack('styles')
</head>
    <body>
    <div class="wrapper">
        @include('admin.layouts.sidebar')
        
        <div class="main-panel">
            @include('admin.layouts.header')
            
            <div class="content">
                @yield('content')
            </div>
            
            @include('admin.layouts.footer')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('kazaria-admin/assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('kazaria-admin/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('kazaria-admin/assets/js/core/bootstrap.min.js') }}"></script>
    
    <!-- jQuery Scrollbar -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    
    <!-- Chart JS -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/chart.js/chart.min.js') }}"></script>
    
    <!-- jQuery Sparkline -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
    
    <!-- Chart Circle -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/chart-circle/circles.min.js') }}"></script>
    
    <!-- Datatables -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/datatables/datatables.min.js') }}"></script>
    
    <!-- Bootstrap Notify -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    
    <!-- jQuery Vector Map -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/vector-map/jquery-jvectormap-2.0.3.min.js') }}"></script>
    <script src="{{ asset('kazaria-admin/assets/js/plugin/vector-map/jquery-jvectormap-world-mill.js') }}"></script>
    
    <!-- Sweet Alert -->
    <script src="{{ asset('kazaria-admin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    
    <!-- Kaiadmin JS -->
    <script src="{{ asset('kazaria-admin/assets/js/kaiadmin.min.js') }}"></script>
    
    <!-- Demo JS -->
    <script src="{{ asset('kazaria-admin/assets/js/demo.js') }}"></script>
    
    @stack('scripts')
</body>
</html>

