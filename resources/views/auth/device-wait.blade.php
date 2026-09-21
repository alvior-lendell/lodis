<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Waiting for Authorization — LODISv2</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full p-8 bg-white rounded-3xl border border-slate-200 text-center space-y-6 shadow-xl relative overflow-hidden">
        
        @if ($hasTrustedDevices)
            <div class="w-16 h-16 bg-brand-light text-brand rounded-full flex items-center justify-center mx-auto animate-pulse">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Approve this Login</h2>
                <p class="text-xs text-slate-500 mt-1">Please check your active logged-in device to authorize this sign-in request in real time.</p>
            </div>
        @else
            <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Workstation Verification Required</h2>
                <p class="text-xs text-slate-500 mt-1">This workstation is unrecognized. Select a verification method to complete sign-in.</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 text-left">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Hidden form auto-submitted by Reverb Echo listener -->
        <form id="finalize-form" method="POST" action="{{ route('device.finalize', $auth->id) }}">
            @csrf
        </form>

        <!-- Verification Method Options -->
        <div class="pt-4 border-t border-slate-100 space-y-2">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Authorize via Security Channel</p>

            <!-- Option 1: Email OTP -->
            <form method="POST" action="{{ route('device.start-verification', $auth->id) }}">
                @csrf
                <input type="hidden" name="method" value="email">
                <button type="submit" class="w-full py-2.5 px-4 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/80 transition flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email OTP Code
                    </span>
                    <span class="text-[10px] text-slate-400">Send Code</span>
                </button>
            </form>

            <!-- Option 2: SMS Mobile Match -->
            @if ($hasSms)
                <form method="POST" action="{{ route('device.start-verification', $auth->id) }}">
                    @csrf
                    <input type="hidden" name="method" value="sms">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/80 transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Confirm Registered Mobile
                        </span>
                        <span class="text-[10px] text-slate-400">SMS / Profile</span>
                    </button>
                </form>
            @endif

            <!-- Option 3: Authenticator App (TOTP) -->
            @if ($hasAuthenticator)
                <form method="POST" action="{{ route('device.start-verification', $auth->id) }}">
                    @csrf
                    <input type="hidden" name="method" value="authenticator">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/80 transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Authenticator App (TOTP)
                        </span>
                        <span class="text-[10px] text-slate-400">2FA App</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($hasTrustedDevices)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof window.Echo !== 'undefined') {
                    window.Echo.channel('device-auth.{{ $auth->id }}')
                        .listen('.device.status.changed', (e) => {
                            if (e.status === 'approved') {
                                document.getElementById('finalize-form').submit();
                            } else if (e.status === 'rejected') {
                                // Pass query parameter to trigger rejection alert banner on welcome page
                                window.location.href = "{{ route('login') }}?rejected=1";
                            }
                        });
                }
            });
        </script>
    @endif
</body>
</html>