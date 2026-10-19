<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Device Verification - LODISv2</title>

    <!-- Favicon & PWA Directives -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LODISv2.png') }}">
    <meta name="theme-color" content="#00687A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LODISv2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Leaflet.js Mapping Library (CDN) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Leaflet Image Reset Fix -->
    <style>
        .leaflet-container img {
            max-width: none !important;
            max-height: none !important;
        }
    </style>

    <!-- Compiled Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 flex flex-col selection:bg-brand selection:text-white">

    <!-- Universal Header Include -->
    <div id="app" class="flex-1 flex flex-col">
        @include('partials.header')

        <!-- Main Workspace Content -->
        <main class="flex-1 max-w-2xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col justify-center">
            <div id="app">
                <device-verification-view
                    :auth='@json($authFormatted)'
                    status-message="{{ session('status') ?? '' }}"
                    csrf-token="{{ csrf_token() }}"
                    :routes='@json($routes)'
                ></device-verification-view>
            </div>
        </main>

    </div>

    <!-- Universal Footer Include -->
    @include('partials.footer')

    @include('partials.it-support-widget')

    <!-- Leaflet.js Mapping Library Script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

</body>
</html>