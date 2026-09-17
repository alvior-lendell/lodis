@php
    $selectedMethod = old('method', 'email');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Reset Password — LODISv2</title>

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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 relative selection:bg-brand selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- Form Panel -->
        <div class="order-1 lg:order-2 lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white relative">
            <div id="auth-container" class="w-full max-w-md transition-all duration-200">
                
                <!-- Mobile Brand Header -->
                <div class="lg:hidden text-center mb-8">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-12 w-auto mx-auto mb-3 object-contain">
                    <p class="text-[11px] font-bold text-brand uppercase tracking-widest">Lendell Digital Ecosystem</p>
                </div>

                <!-- Form Title -->
                <div class="mb-8 text-center lg:text-left">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reset Password</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Select your preferred verification channel to initiate identity authentication and password recovery.
                    </p>
                </div>

                <!-- Session Status Alert -->
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

                <!-- Password Reset Request Form -->
                <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}" autocomplete="off" class="space-y-5 transition-opacity duration-200">
                    @csrf

                    <!-- Email Address Input -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Registered Email Address</label>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="off"
                                class="form-input block w-full pl-11 pr-4 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400"
                                placeholder="email@lendell.ph">
                        </div>
                    </div>

                    <!-- Verification Channel Selection -->
                    <div class="space-y-3 pt-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Select Reset Channel</label>
                        
                        <!-- Email Option -->
                        <label class="block p-3.5 rounded-2xl border cursor-pointer transition flex items-center justify-between border-slate-200 hover:border-brand hover:bg-slate-50/50 has-[:checked]:border-brand has-[:checked]:bg-brand/5 has-[:checked]:ring-2 has-[:checked]:ring-brand/15">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">Email Verification Link</span>
                                    <span class="block text-[11px] text-slate-500">Receive password reset instructions via inbox</span>
                                </div>
                            </div>
                            <input type="radio" name="method" value="email" {{ $selectedMethod === 'email' ? 'checked' : '' }} class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
                        </label>

                        <!-- SMS Option -->
                        <label class="block p-3.5 rounded-2xl border cursor-pointer transition flex items-center justify-between border-slate-200 hover:border-brand hover:bg-slate-50/50 has-[:checked]:border-brand has-[:checked]:bg-brand/5 has-[:checked]:ring-2 has-[:checked]:ring-brand/15">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">Mobile Phone Confirmation (SMS)</span>
                                    <span class="block text-[11px] text-slate-500">Confirm linked contact number to reset</span>
                                </div>
                            </div>
                            <input type="radio" name="method" value="sms" {{ $selectedMethod === 'sms' ? 'checked' : '' }} class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
                        </label>

                        <!-- Authenticator App (TOTP) Option -->
                        <label class="block p-3.5 rounded-2xl border cursor-pointer transition flex items-center justify-between border-slate-200 hover:border-brand hover:bg-slate-50/50 has-[:checked]:border-brand has-[:checked]:bg-brand/5 has-[:checked]:ring-2 has-[:checked]:ring-brand/15">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">Authenticator App (TOTP)</span>
                                    <span class="block text-[11px] text-slate-500">Validate code via 2FA authenticator app</span>
                                </div>
                            </div>
                            <input type="radio" name="method" value="authenticator" {{ $selectedMethod === 'authenticator' ? 'checked' : '' }} class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
                        </label>
                    </div>

                    <button id="submit-btn" type="submit" 
                        class="w-full mt-6 py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-85 disabled:cursor-not-allowed">
                        <svg id="btn-spinner" class="hidden animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btn-label">Continue Recovery Process</span>
                    </button>
                </form>

                <!-- Back to Sign In Link -->
                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-bold text-brand hover:text-brand-hover transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to System Sign In
                    </a>
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
                        Account Recovery
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                        Secure Credential Recovery
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-8">
                        Restore access to your Lendell Online Digital Interactive System account via multi-channel identity verification.
                    </p>

                    <div class="p-4 rounded-2xl bg-white/90 backdrop-blur-md border border-white shadow-md space-y-2">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Having trouble?</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            If you no longer have access to your registered email address or active 2FA device, contact system administration directly.
                        </p>
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