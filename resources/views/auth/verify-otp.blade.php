<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verify Identity — LODISv2</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LODISv2.png') }}">
    <meta name="theme-color" content="#00687A">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 selection:bg-brand selection:text-white">

    <div id="app" class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- Verification Code Panel -->
        <div class="order-1 lg:order-2 lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white relative">
            <div id="auth-container" class="w-full max-w-md transition-all duration-200">
                
                <!-- Mobile Brand Header -->
                <div class="lg:hidden text-center mb-6">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-12 w-auto mx-auto mb-3 object-contain">
                    <p class="text-[11px] font-bold text-brand uppercase tracking-widest">Lendell Digital Ecosystem</p>
                </div>

                <!-- Session Messages -->
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-800 flex items-start gap-3 shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700 shadow-sm">
                        <ul class="list-disc list-inside space-y-1 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Vue Verification Form Component -->
                <verify-otp-form
                    action-url="{{ route('register.otp.verify') }}"
                    resend-action-url="{{ route('register.otp.resend') }}"
                    switch-method-url="{{ route('register.otp.select') }}"
                    csrf-token="{{ csrf_token() }}"
                    method="{{ $method }}"
                    email="{{ $email }}"
                    phone-number="{{ $phoneNumber ?? '' }}"
                    qr-code-url="{{ $qrCodeUrl ?? '' }}"
                    secret-key="{{ $secretKey ?? '' }}"
                    cooldown-seconds="{{ $cooldownSeconds ?? 60 }}"
                ></verify-otp-form>

            </div>
        </div>

        <!-- Branding Panel -->
        <div class="order-2 lg:order-1 lg:w-1/2 bg-gradient-to-br from-brand-light via-slate-50 to-brand-light/50 border-t lg:border-t-0 lg:border-r border-slate-200/80 p-6 sm:p-10 lg:p-16 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-12 -left-12 w-64 h-64 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-12 -right-12 w-64 h-64 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="hidden lg:block mb-12">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-16 lg:h-20 w-auto object-contain">
                </div>

                <div class="max-w-md my-auto">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-white text-brand border border-slate-200/80 shadow-sm mb-6">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Security Verification
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                        Employee Identity Verification
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-8">
                        Complete identity verification to finalize account provisioning and unlock access to employee services.
                    </p>
                </div>
            </div>

            <div class="mt-8 sm:mt-12 pt-6 border-t border-slate-200/60 text-xs text-slate-500 flex justify-between items-center relative z-10 font-medium">
                <span>&copy; {{ date('Y') }} Lendell Outsourcing Solutions, Inc. All rights reserved.</span>
                <span class="font-mono text-slate-400">v2.1.0</span>
            </div>
        </div>

    </div>

</body>
</html>