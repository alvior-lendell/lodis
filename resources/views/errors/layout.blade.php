<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - LODISv2</title>

    <!-- Favicon & Directives -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LODISv2.png') }}">
    <meta name="theme-color" content="#00687A">

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 flex items-center justify-center p-6 relative overflow-hidden selection:bg-brand selection:text-white">

    <!-- Background Decorative Blurs -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg bg-white rounded-3xl border border-slate-200/80 shadow-xl overflow-hidden relative z-10 transition-all">
        
        <!-- Top Accent Bar -->
        <div class="h-2 bg-gradient-to-r from-brand via-brand-hover to-brand-accent"></div>

        <div class="p-8 sm:p-10 text-center space-y-6">
            
            <!-- App Branding -->
            <div class="flex items-center justify-center gap-2 mb-2">
                <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-10 w-auto object-contain">
            </div>

            <!-- Error Code Pill -->
            <div>
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-widest bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                    @yield('code_badge')
                </span>
            </div>

            <!-- Main Heading & Message -->
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    @yield('title')
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed max-w-sm mx-auto">
                    @yield('message')
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" 
                   class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition duration-150 ease-in-out flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Return to Home</span>
                </a>
                
                <button type="button" onclick="window.location.reload()" 
                        class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition duration-150 ease-in-out flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Reload Page</span>
                </button>
            </div>

        </div>

        <!-- Card Footer -->
        <div class="bg-slate-50 border-t border-slate-100 px-8 py-4 text-center text-[11px] text-slate-400 font-medium">
            Lendell Online Digital Interactive System &bull; System Status Alert
        </div>
    </div>

</body>
</html>