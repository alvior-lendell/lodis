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
        #device-map {
            height: 220px !important;
            width: 100% !important;
            z-index: 1 !important;
        }
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
    @include('partials.header')

    <!-- Main Workspace Content -->
    <main class="flex-1 max-w-2xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col justify-center">

        <!-- Status Toast Alert -->
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <!-- Device Authorization Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl overflow-hidden">
            
            <!-- Brand Header Banner -->
            <div class="bg-gradient-to-r from-brand to-brand-hover text-white p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 text-amber-300 border border-white/15 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-white/15 text-white border border-white/20 uppercase tracking-wider mb-1">
                            Security Review
                        </span>
                        <h1 class="text-lg font-extrabold text-white tracking-tight">Workstation Sign-In Request</h1>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Request Details Grid -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</span>
                        @if ($auth->status === 'pending' && !$auth->expires_at->isPast())
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                Awaiting Authorization
                            </span>
                        @elseif ($auth->status === 'approved')
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Authorized & Trusted
                            </span>
                        @elseif ($auth->status === 'rejected')
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                Access Rejected
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                Request Expired
                            </span>
                        @endif
                    </div>

                    <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-200/80 space-y-3.5 text-xs">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 font-medium">Workstation / Device</span>
                            <span class="font-bold text-slate-900 text-right">{{ $auth->device_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 font-medium">IP Address</span>
                            <span class="font-mono font-bold text-brand">{{ $auth->ip_address }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 font-medium">Request Time</span>
                            <span class="text-slate-800 font-semibold">{{ $auth->created_at->format('M d, Y h:i A') }} ({{ $auth->created_at->diffForHumans() }})</span>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-4">
                            <span class="text-slate-500 font-medium">Approximate Location</span>
                            <span class="font-semibold text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $auth->location ?? 'Unknown Location' }}</span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 font-medium">Geo Coordinates</span>
                            @if ($auth->latitude && $auth->longitude)
                                <span class="font-mono text-[11px] font-bold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                    {{ number_format($auth->latitude, 4) }}, {{ number_format($auth->longitude, 4) }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Unavailable</span>
                            @endif
                        </div>
                    </div>

                    <!-- Map Render Block -->
                    @if ($auth->latitude && $auth->longitude)
                        <div class="space-y-2">
                            <div id="device-map" class="rounded-2xl border border-slate-200 overflow-hidden shadow-inner"></div>

                            <div class="flex items-center justify-between text-[11px] text-slate-500 px-1">
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    Target IP Geolocation
                                </span>
                                <a href="https://www.google.com/maps?q={{ $auth->latitude }},{{ $auth->longitude }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="font-bold text-brand hover:text-brand-hover hover:underline inline-flex items-center gap-1">
                                    <span>Open in Google Maps</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Action Buttons -->
                @if ($auth->status === 'pending' && !$auth->expires_at->isPast())
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        <form method="POST" action="{{ route('device.reject', $auth->id) }}" class="w-full sm:w-1/2">
                            @csrf
                            <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Reject Access</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('device.approve', $auth->id) }}" class="w-full sm:w-1/2">
                            @csrf
                            <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Authorize & Trust Device</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="pt-2 text-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-brand text-xs font-bold transition">
                            <span>Return to Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </main>

    <!-- Universal Footer Include -->
    @include('partials.footer')

    <!-- Leaflet.js Mapping Library Script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @if ($auth->latitude && $auth->longitude)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lat = {{ $auth->latitude }};
            const lng = {{ $auth->longitude }};

            const map = L.map('device-map', {
                zoomControl: true,
                scrollWheelZoom: false,
            }).setView([lat, lng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup('<b>Location Target</b><br>{{ $auth->location }}')
                .openPopup();

            // Force recalculation of map container dimensions
            setTimeout(() => {
                map.invalidateSize();
            }, 200);
        });
    </script>
    @endif

</body>
</html>