<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - LODISv2</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LODISv2.png') }}">
    <meta name="theme-color" content="#00687A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 relative selection:bg-brand selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Verification Form Panel -->
        <div class="order-1 lg:order-2 lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white relative">
            <div id="auth-container" class="w-full max-w-md transition-all duration-200">

                <div class="lg:hidden text-center mb-8">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-12 w-auto mx-auto mb-3 object-contain">
                    <p class="text-[11px] font-bold text-brand uppercase tracking-widest">Lendell Digital Ecosystem</p>
                </div>

                <div class="mb-8 text-center lg:text-left">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $title }}</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        We sent a 6-digit verification code via SMS to your registered mobile number ending in <span class="font-bold text-slate-700">{{ session('masked_phone', '••••') }}</span>.
                    </p>
                </div>

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

                <form id="sms-verify-form" method="POST" action="{{ $verifyRoute }}" autocomplete="off" class="space-y-6 transition-opacity duration-200">
                    @csrf

                    <div>
                        <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-center lg:text-left">6-Digit Security Code</label>
                        <div class="relative rounded-2xl shadow-sm">
                            <input id="code" type="text" name="code" required autofocus maxlength="6" pattern="[0-9]*" inputmode="numeric" autocomplete="one-time-code"
                                class="form-input block w-full py-4 px-4 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-center font-mono text-2xl tracking-[0.4em] outline-none transition placeholder:text-slate-300 placeholder:tracking-normal"
                                placeholder="000000">
                        </div>
                    </div>

                    <button id="submit-btn" type="submit" disabled
                        class="w-full py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg id="btn-spinner" class="hidden animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btn-label">{{ $buttonLabel }}</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 text-center space-y-3">
                    <form id="resend-form" method="POST" action="{{ $resendRoute }}">
                        @csrf
                        @if (session('pending_reset_user_id'))
                            <input type="hidden" name="phone" value="{{ session('phone', '') }}">
                        @endif
                        <p class="text-xs text-slate-500 font-medium">
                            Didn't receive the SMS code?
                            <button type="submit" id="resend-btn" class="font-bold text-brand hover:text-brand-hover transition ml-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                Resend Code <span id="resend-timer"></span>
                            </button>
                        </p>
                    </form>

                    <div>
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition">
                                    Sign out of session
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition">
                                Back to Sign In
                            </a>
                        @endauth
                    </div>
                </div>

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
                        Phone Verification Required
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-8">
                        SMS verification ensures identity confirmation before granting password resets or administrative access across internal subsystems.
                    </p>
                </div>
            </div>

            <div class="mt-8 sm:mt-12 pt-6 border-t border-slate-200/60 text-xs text-slate-500 flex justify-between items-center relative z-10 font-medium">
                <span>&copy; {{ date('Y') }} Lendell Outsourcing Solutions, Inc. All rights reserved.</span>
                <span class="font-mono text-slate-400">v2.1.0</span>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const codeInput = document.getElementById('code');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.getElementById('sms-verify-form');
            const btnSpinner = document.getElementById('btn-spinner');
            const btnLabel = document.getElementById('btn-label');

            codeInput?.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                submitBtn.disabled = e.target.value.length !== 6;
                if (e.target.value.length === 6) {
                    form.requestSubmit();
                }
            });

            form?.addEventListener('submit', () => {
                submitBtn.disabled = true;
                btnSpinner.classList.remove('hidden');
                btnLabel.textContent = 'Verifying Code...';
            });

            const resendBtn = document.getElementById('resend-btn');
            const resendTimer = document.getElementById('resend-timer');
            let countdown = 60;

            if (resendBtn && resendTimer) {
                resendBtn.disabled = true;
                const interval = setInterval(() => {
                    countdown--;
                    resendTimer.textContent = `(${countdown}s)`;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        resendTimer.textContent = '';
                        resendBtn.disabled = false;
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>