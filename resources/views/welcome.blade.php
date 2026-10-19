<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $isSubsystem ? $systemName . ' — Single Sign-On Portal' : 'LODISv2 - Lendell Online Digital Interactive System' }}</title>

    <!-- Favicon & Clean PWA Directives -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ $systemLogo }}">
    <meta name="theme-color" content="{{ $brandColor }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $systemName }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Compiled Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Cloudflare Turnstile API -->
    @if (config('services.turnstile.key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback&render=explicit" async defer></script>
    @endif
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 relative selection:bg-brand selection:text-white">

    <!-- Desktop panels grow together when their content exceeds the viewport. -->
    <div id="app" class="min-h-screen flex flex-col lg:flex-row">

        <!-- Login Panel (Left Side) -->
        <div class="order-1 lg:order-2 lg:w-1/2 lg:min-h-screen flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white relative z-10">
            <div id="auth-container" class="w-full max-w-md transition-all duration-200 my-auto">

                <!-- Device Rejection Alert -->
                @if (session('error') || session('rejected') || request()->has('rejected'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs text-rose-800 flex items-start gap-3 shadow-sm">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-rose-900">Access Request Rejected</h4>
                            <p class="font-medium leading-relaxed text-rose-700">
                                {{ session('error') ?? session('rejected') ?? (is_string(request('rejected')) && request('rejected') !== '1' ? request('rejected') : 'Your workstation authorization request was rejected.') }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Session Status Message -->
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-800 flex items-start gap-3 shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700 shadow-sm">
                        <ul class="list-disc list-inside space-y-1 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Vue Login Form Component -->
                <login-form 
                    action-url="{{ route('login') }}" 
                    csrf-token="{{ csrf_token() }}"
                    old-login="{{ old('login') }}"
                    :old-remember="{{ old('remember') ? 'true' : 'false' }}"
                    forgot-password-url="{{ Route::has('password.request') ? route('password.request') : '' }}"
                    system-name="{{ $systemName }}"
                    system-logo="{{ $systemLogo }}"
                    brand-color="{{ $brandColor }}"
                    brand-hover-color="{{ $brandHoverColor }}"
                >
                    <template #turnstile>
                        @if (config('services.turnstile.key'))
                            <div class="my-4 p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex flex-col items-center justify-center gap-2 shadow-inner">
                                <div id="turnstile-container" 
                                    class="w-full flex justify-center" 
                                    data-sitekey="{{ config('services.turnstile.key') }}">
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium text-center">
                                    Protected by Cloudflare Turnstile Zero-Trust Access
                                </p>
                            </div>
                        @endif
                    </template>
                </login-form>

                <div id="register-footer" class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-600 font-medium">
                        Need access to {{ $systemName }}?
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-bold transition ml-1 hover:underline" style="color: {{ $brandColor }}">
                                Request Account
                            </a>
                        @else
                            Contact your system administrator to provision an account.
                        @endif
                    </p>
                </div>

            </div>
        </div>

        <!-- Dynamic Slideshow Panel (Right Side) -->
        <div class="order-2 lg:order-1 lg:w-1/2 lg:min-h-screen relative overflow-hidden min-h-[560px] shrink-0">
            <system-slideshow
                :systems='@json($systems)'
                :is-subsystem="{{ $isSubsystem ? 'true' : 'false' }}"
                current-system-name="{{ $systemName }}"
            >
                <!-- Desktop PWA Container Slot Pass-through -->
                <template #pwa-slot>
                    <div id="pwa-install-container" class="hidden mb-8 p-4 rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-xl flex items-center justify-between gap-4 transition-all duration-300 hover:border-white/30">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-white/10 border border-white/20">
                                <img src="{{ asset('images/logo.png') }}" alt="LODISv2" class="w-7 h-7 object-contain">
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Install LODISv2 Web App</h4>
                                <p class="text-[11px] text-slate-300">Launch directly from your desktop or home screen.</p>
                            </div>
                        </div>
                        <button id="pwa-install-btn" type="button" class="px-4 py-2 rounded-xl bg-white text-slate-900 hover:bg-slate-100 active:scale-95 text-xs font-bold shadow-md transition flex items-center gap-1.5 shrink-0">
                            <svg class="w-4 h-4 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Install</span>
                        </button>
                    </div>
                </template>
            </system-slideshow>
        </div>

    </div>

    <!-- Refactored PWA Script -->
    <script>
        (function () {
            let deferredPrompt = null;

            // 1. Register 'beforeinstallprompt' immediately on window to capture early events
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('[PWA] beforeinstallprompt event captured.');
                e.preventDefault();
                deferredPrompt = e;

                // Dynamically fetch container (handles Vue rendering delays)
                const installContainer = document.getElementById('pwa-install-container');
                if (installContainer) {
                    installContainer.classList.remove('hidden');
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
                if (isStandalone) {
                    console.log('[PWA] Running in standalone mode.');
                    return;
                }

                // Fallback check if the event was captured before Vue mounted
                if (deferredPrompt) {
                    const installContainer = document.getElementById('pwa-install-container');
                    if (installContainer) {
                        installContainer.classList.remove('hidden');
                    }
                }

                // Event Delegation for Install Button (handles Vue slot re-renders)
                document.addEventListener('click', async (e) => {
                    const installBtn = e.target.closest('#pwa-install-btn');
                    if (!installBtn) return;

                    if (!deferredPrompt) {
                        console.warn('[PWA] No install prompt event stored.');
                        return;
                    }

                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`[PWA] Install prompt outcome: ${outcome}`);

                    deferredPrompt = null;
                    const installContainer = document.getElementById('pwa-install-container');
                    if (installContainer) {
                        installContainer.classList.add('hidden');
                    }
                });

                // Handle successful app installation
                window.addEventListener('appinstalled', () => {
                    console.log('[PWA] App successfully installed.');
                    deferredPrompt = null;
                    const installContainer = document.getElementById('pwa-install-container');
                    if (installContainer) {
                        installContainer.classList.add('hidden');
                    }
                });

                // Service Worker Registration
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/sw.js')
                        .then(reg => console.log('[PWA] Service Worker registered:', reg.scope))
                        .catch(err => console.error('[PWA] Service Worker registration failed:', err));
                }
            });
        })();
    </script>
</body>
</html>