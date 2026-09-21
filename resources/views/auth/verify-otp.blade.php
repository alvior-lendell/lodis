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

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 selection:bg-brand selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- Verification Code Panel -->
        <div class="order-1 lg:order-2 lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white relative">
            <div id="auth-container" class="w-full max-w-md transition-all duration-200">
                
                <!-- Mobile Brand Header -->
                <div class="lg:hidden text-center mb-6">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-12 w-auto mx-auto mb-3 object-contain">
                    <p class="text-[11px] font-bold text-brand uppercase tracking-widest">Lendell Digital Ecosystem</p>
                </div>

                <!-- Form Header with Switch Method Link -->
                <div class="mb-6 text-center lg:text-left">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            @if ($method === 'sms')
                                Confirm Mobile Number
                            @elseif ($method === 'authenticator')
                                Authenticator Code
                            @else
                                Enter Security Code
                            @endif
                        </h3>
                        <a href="{{ route('register.otp.select') }}" class="text-xs font-bold text-brand hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                            Change Method
                        </a>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500">
                        @if ($method === 'sms')
                            Enter the mobile phone number registered under your employee profile.
                        @else
                            Enter the 6-digit verification code to activate your account.
                        @endif
                    </p>
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

                <!-- Authenticator App QR Setup Banner -->
                @if ($method === 'authenticator')
                    <div class="mb-6 p-5 rounded-3xl bg-slate-50 border border-slate-200 space-y-4 text-center">
                        <div class="text-left space-y-1">
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Authenticator Setup</h4>
                            <p class="text-xs text-slate-500">Scan this QR code using Google Authenticator, 1Password, or Authy, then enter the code below.</p>
                        </div>

                        @if ($qrCodeUrl)
                            <div class="p-3 bg-white rounded-2xl inline-block shadow-sm border border-slate-200/80">
                                <img src="{{ $qrCodeUrl }}" alt="Authenticator QR Code" class="w-44 h-44 mx-auto object-contain">
                            </div>
                        @endif

                        @if ($secretKey)
                            <div class="bg-white p-3 rounded-2xl border border-slate-200 text-center space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Manual Key Entry</span>
                                <code class="text-xs font-mono font-bold text-brand select-all tracking-wider block">{{ $secretKey }}</code>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Channel Info Banner -->
                    <div class="mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 font-medium leading-relaxed">
                        @if ($method === 'sms')
                            To verify your identity, enter the mobile number linked to your employee record (e.g. <strong>09XXXXXXXXX</strong>).
                        @else
                            Code dispatched via Email to <strong class="text-slate-800">{{ $email }}</strong>.
                        @endif
                    </div>
                @endif

                <!-- Verification Form -->
                <form id="verify-otp-form" method="POST" action="{{ route('register.otp.verify') }}" autocomplete="off" class="space-y-5 transition-opacity duration-200">
                    @csrf

                    <div>
                        <label for="otp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            @if ($method === 'authenticator')
                                6-Digit Authenticator Code
                            @elseif ($method === 'sms')
                                Registered Mobile Number
                            @else
                                6-Digit Verification Code
                            @endif
                        </label>
                        <div class="relative rounded-2xl shadow-sm">
                            <input id="otp" type="{{ $method === 'sms' ? 'tel' : 'text' }}" name="otp" 
                                maxlength="{{ $method === 'sms' ? '13' : '6' }}" required autofocus autocomplete="off"
                                class="form-input block w-full text-center text-xl sm:text-2xl font-mono tracking-widest px-4 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 outline-none transition font-semibold placeholder:text-slate-300 placeholder:tracking-normal"
                                placeholder="{{ $method === 'sms' ? '0917XXXXXXX' : '000000' }}">
                        </div>
                    </div>

                    <button id="verify-btn" type="submit" 
                        class="w-full py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-85 disabled:cursor-not-allowed">
                        <svg id="btn-spinner" class="hidden animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btn-label">Verify & Complete Account</span>
                    </button>
                </form>

                <!-- Resend Form with Timer (Email Only) -->
                @if ($method === 'email')
                    <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                        <span>Didn't receive the code?</span>
                        <form id="resend-otp-form" method="POST" action="{{ route('register.otp.resend') }}" autocomplete="off">
                            @csrf
                            <button id="resend-btn" type="submit" disabled data-cooldown="{{ $cooldown }}" class="font-bold text-slate-400 cursor-not-allowed transition disabled:opacity-60 flex items-center gap-1.5">
                                <svg id="resend-spinner" class="hidden animate-spin h-3.5 w-3.5 text-brand shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="resend-label">Resend Code <span id="timer-label">({{ $cooldown }}s)</span></span>
                            </button>
                        </form>
                    </div>
                @endif

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