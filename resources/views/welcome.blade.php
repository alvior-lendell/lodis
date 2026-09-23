<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LODISv2 - Lendell Online Digital Interactive System</title>

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

    <!-- Compiled Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'])

    <!-- Cloudflare Turnstile API -->
    @if (config('services.turnstile.key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 relative selection:bg-brand selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Login Panel -->
        <div class="order-1 lg:order-2 lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white relative">
            <div id="auth-container" class="w-full max-w-md transition-all duration-200">

                <!-- Mobile Brand Header -->
                <div class="lg:hidden text-center mb-8">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-12 w-auto mx-auto mb-3 object-contain">
                    <p class="text-[11px] font-bold text-brand uppercase tracking-widest">Lendell Digital Ecosystem</p>
                </div>

                <!-- Form Title -->
                <div class="mb-8 text-center lg:text-left">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Secure Sign In</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Enter your credentials to access the application launcher.</p>
                </div>

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

                <!-- Login Form -->
                <form id="login-form" method="POST" action="{{ route('login') }}" autocomplete="off" class="space-y-5 transition-opacity duration-200">
                    @csrf

                    <div>
                        <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Employee ID or Email</label>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="off"
                                class="form-input block w-full pl-11 pr-4 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400"
                                placeholder="Employee ID or Email">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                            @if (Route::has('password.request'))
                                <a id="forgot-password-link" href="{{ route('password.request') }}" class="text-xs font-bold text-brand hover:text-brand-hover hover:underline transition">Forgot?</a>
                            @endif
                        </div>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                data-caps-warning="caps-lock-warning"
                                class="form-input block w-full pl-11 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400"
                                placeholder="••••••••">

                            <!-- Password Toggle Button -->
                            <button type="button" id="toggle-password-btn" data-toggle-password="password" title="Toggle password visibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand focus:text-brand outline-none transition z-10">
                                <svg id="eye-icon" class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eye-slash-icon" class="w-5 h-5 hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Caps Lock Warning -->
                        <div id="caps-lock-warning" class="hidden text-[11px] font-bold text-amber-600 mt-1.5 flex items-center gap-1.5 animate-pulse">
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Caps Lock is turned ON</span>
                        </div>
                    </div>

                    <!-- Remember Me Toggle Switch -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember" class="flex items-center gap-3 cursor-pointer select-none group py-1">
                            <div class="relative">
                                <input type="checkbox" id="remember" name="remember" class="sr-only peer" {{ old('remember') ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand/15 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-600 group-hover:text-slate-900 transition">Keep me signed in</span>
                        </label>
                    </div>

                    <!-- Cloudflare Turnstile Field -->
                    @if (config('services.turnstile.key'))
                        <div class="my-4 p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex flex-col items-center justify-center gap-2 shadow-inner">
                            <div class="cf-turnstile w-full flex justify-center" 
                                 data-sitekey="{{ config('services.turnstile.key') }}" 
                                 data-theme="light"
                                 data-size="flexible"
                                 data-appearance="interaction-only">
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium text-center">
                                Protected by Cloudflare Turnstile Zero-Trust Access
                            </p>
                        </div>
                    @endif

                    <button id="submit-btn" type="submit"
                        class="w-full py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-85 disabled:cursor-not-allowed">
                        <svg id="btn-spinner" class="hidden animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btn-label">Authenticate & Sign In</span>
                    </button>
                </form>

                <div id="register-footer" class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500 font-medium">
                        Need access to LODISv2?
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-bold text-brand hover:text-brand-hover transition ml-1">
                                Create an account
                            </a>
                        @else
                            Contact your system administrator to provision an account.
                        @endif
                    </p>
                </div>

            </div>
        </div>

        <!-- Branding Panel -->
        <div class="order-2 lg:order-1 lg:w-1/2 bg-gradient-to-br from-brand-light via-slate-50 to-brand-light/50 border-t lg:border-t-0 lg:border-r border-slate-200/80 p-6 sm:p-10 lg:p-16 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-12 -left-12 w-64 h-64 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-12 -right-12 w-64 h-64 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="hidden lg:block mb-12">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2 - Lendell Online Digital Interactive System" class="h-16 lg:h-20 w-auto object-contain">
                </div>

                <div class="max-w-md my-auto">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-white text-brand border border-slate-200/80 shadow-sm mb-6">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Central Web Application Hub
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                        Lendell Online Digital Interactive System
                    </h2>
                    <p class="text-slate-700 text-sm leading-relaxed mb-4">
                        Secure single-sign-on launcher providing centralized access to internal operational platforms, data pipelines, and administrative management applications — your unified gateway to the Lendell enterprise ecosystem.
                    </p>
                    <p class="text-slate-500 text-xs sm:text-sm leading-relaxed mb-8">
                        Access all authorized systems: Lendell Online Payroll Integrated System, Lendell Online DocCrypt Suite, Lendell Online Training Integrated System, Lendell Online Standards Integrated System, Lendell Online Resources Integrated System, Lendell Online Billing Integrated System, Lendell Online Marketing Integrated System, and Lendell - Executive Command Center for CEO.
                    </p>

                    <!-- PWA Native Install Action Card -->
                    <div id="pwa-install-container" class="hidden mb-8 p-4 rounded-2xl bg-white/90 backdrop-blur-md border border-white shadow-md flex items-center justify-between gap-3 transition">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo.png') }}" alt="LODISv2" class="w-8 h-8 object-contain shrink-0">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">Install LODISv2 Web App</h4>
                                <p class="text-[11px] text-slate-500">Launch directly from your desktop or home screen.</p>
                            </div>
                        </div>
                        <button id="pwa-install-btn" type="button"
                            class="px-3.5 py-2 rounded-xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-xs font-bold shadow-sm transition shrink-0 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Install</span>
                        </button>
                    </div>

                    <!-- Feature Highlights -->
                    <div class="hidden sm:space-y-4">
                        <div class="flex items-start gap-3.5">
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-brand shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Unified Access</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Launch company applications with a single authenticated session.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-brand shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Role-Based Security</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Strictly managed workspace access enforced across all subsystems.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="mt-8 sm:mt-12 pt-6 border-t border-slate-200/60 text-xs text-slate-500 flex justify-between items-center relative z-10 font-medium">
                <span>&copy; {{ date('Y') }} Lendell Outsourcing Solutions, Inc. All rights reserved.</span>
                <span class="font-mono text-slate-400">v2.1.0</span>
            </div>
        </div>

    </div>
</body>
</html>