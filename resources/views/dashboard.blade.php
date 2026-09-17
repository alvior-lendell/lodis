<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token for Client-Side Fetch Requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Dashboard - LODISv2</title>

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
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 flex flex-col">

    <!-- Universal Header Include -->
    @include('partials.header')

    <!-- Main Workspace Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Status Toast Banner -->
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

        <!-- Universal Welcome Banner -->
        <div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-brand to-brand-hover text-white shadow-md relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left Info: Greetings & Live Clock -->
            <div class="z-10">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-light/80">
                    <span id="live-date">--</span>
                    <span>•</span>
                    <span id="live-clock" class="font-mono">--:--:-- --</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1" id="greeting-heading">
                    Welcome back, {{ $greetingName }}
                </h2>
                <p class="text-xs text-brand-light/90 mt-1">Access your operational applications and system modules below.</p>
            </div>

            <!-- Right Info: Live Weather & Dynamic Event Tile -->
            <div class="z-10 flex flex-wrap items-center gap-3 sm:gap-4">
                <!-- Live Weather Box -->
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10 flex items-center gap-3 min-w-[150px]">
                    <div id="weather-icon-container" class="text-amber-300 shrink-0">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-extrabold leading-none" id="weather-temp">--°C</span>
                        <span class="text-[10px] text-brand-light/80 block mt-1" id="weather-condition">Mandaluyong City</span>
                    </div>
                </div>

                <!-- Dynamic Same-Day Grouped Event Tile -->
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10 flex items-center gap-3 max-w-xs min-w-[200px]">
                    <div class="p-2 rounded-lg {{ $isToday ? 'bg-emerald-400/20 text-emerald-300' : 'bg-amber-400/20 text-amber-300' }} shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="overflow-hidden w-full">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-[10px] font-semibold uppercase tracking-wider {{ $isToday ? 'text-emerald-200' : 'text-amber-200' }}">
                                {{ $eventType }}
                            </span>
                            @if ($isToday)
                                <span class="px-1.5 py-0.5 text-[9px] font-extrabold uppercase bg-emerald-500/30 text-emerald-200 rounded animate-pulse">
                                    Today
                                </span>
                            @endif
                        </div>

                        <h4 class="text-xs font-bold text-white truncate" title="{{ $combinedTitle ?: 'No upcoming events scheduled' }}">
                            {{ $combinedTitle ?: 'No upcoming events scheduled' }}
                        </h4>

                        @if ($nearestDate)
                            <span class="text-[10px] text-brand-light/80 block mt-0.5">
                                {{ \Carbon\Carbon::parse($nearestDate)->format('M d, Y') }}
                                @if ($distanceLabel && !$isToday)
                                    • <span class="font-bold text-amber-200">{{ $distanceLabel }}</span>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Overview Banner (Superadmin & Admin only) -->
        @if (in_array($userRole, ['Superadmin', 'Admin']))
            <div class="mb-8 p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-brand-light text-brand shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Administrative Metrics</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Overview of active application infrastructure and permission deployments.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                    <div class="text-right">
                        <span class="block text-xs text-slate-500">Total Systems</span>
                        <span class="text-base font-extrabold text-slate-900">{{ $systems->count() }}</span>
                    </div>
                    <div class="h-8 w-px bg-slate-200"></div>
                    <div class="text-right">
                        <span class="block text-xs text-slate-500">User Grants</span>
                        <span class="text-base font-extrabold text-brand">{{ $systems->where('employee_has_access', 1)->count() }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">System Launcher Grid</h2>
                <p class="text-xs text-slate-500 mt-1">Authorized applications are prioritized. Click launch to open your target system workspace.</p>
            </div>
            
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-600 w-fit">
                <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                {{ $systems->where('employee_has_access', 1)->count() }} Authorized Applications
            </span>
        </div>

        <!-- Systems Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($systems as $system)
                @php
                    $hasAccess = (bool) $system->employee_has_access;
                    $role = $system->user_role;
                    $logoPath = $system->logo ? (str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo) : null;
                @endphp

                <div class="bg-white rounded-2xl border transition duration-200 flex flex-col justify-between overflow-hidden shadow-sm hover:shadow-md 
                    {{ $hasAccess ? 'border-slate-200 hover:border-brand/30' : 'border-slate-200/80 bg-slate-50/50 opacity-75' }}">
                    
                    <div class="p-6 flex flex-col justify-between flex-1">
                        <!-- Top Row: Role / Access Badge -->
                        <div class="flex justify-end mb-2">
                            @if ($hasAccess)
                                @if ($role === 'Superadmin')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Superadmin</span>
                                @elseif ($role === 'Admin')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Admin</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Employee</span>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Restricted
                                </span>
                            @endif
                        </div>

                        <!-- Hero Logo Center Piece -->
                        <div class="py-6 flex items-center justify-center min-h-[110px]">
                            @if ($logoPath && file_exists(public_path($logoPath)))
                                <img src="{{ asset($logoPath) }}" alt="{{ $system->name }}" class="max-h-24 w-auto object-contain transition-transform duration-200 hover:scale-105">
                            @else
                                <div class="text-center">
                                    <h3 class="text-xl font-extrabold text-slate-900">{{ $system->name }}</h3>
                                    @if ($system->description)
                                        <p class="text-xs text-slate-500 mt-1">{{ $system->description }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Footer -->
                    <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                        @if ($hasAccess)
                            <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer" 
                                class="w-full py-2.5 px-4 rounded-xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2">
                                <span>Launch Platform</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @else
                            <button type="button" disabled 
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed flex items-center justify-center gap-2">
                                <span>Access Restricted</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-12 rounded-2xl border border-slate-200 text-center">
                    <p class="text-sm font-semibold text-slate-700">No active systems registered in LODISv2.</p>
                    <p class="text-xs text-slate-500 mt-1">Please contact your administrator to configure platform entries.</p>
                </div>
            @endforelse

            <!-- Superadmin Only: Create System Card Tile -->
            @if ($userRole === 'Superadmin')
                <a href="{{ Route::has('systems.create') ? route('systems.create') : '#' }}" 
                    class="border-2 border-dashed border-slate-300 hover:border-brand rounded-2xl p-6 flex flex-col items-center justify-center text-center transition duration-200 group bg-slate-50/50 hover:bg-white min-h-[220px]">
                    <div class="w-12 h-12 rounded-full bg-slate-100 group-hover:bg-brand-light text-slate-400 group-hover:text-brand flex items-center justify-center mb-3 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 group-hover:text-brand transition">Add New System</span>
                    <span class="text-[11px] text-slate-400 mt-1">Register an operational application</span>
                </a>
            @endif
        </div>
    </main>

    <!-- Universal Footer Include -->
    @include('partials.footer')

    <!-- Client Scripts: Clock & Weather -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userGreetingName = @json($greetingName);

            // Time & Greeting Logic
            function updateClockAndGreeting() {
                const now = new Date();
                const hours = now.getHours();

                const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                const dateString = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                document.getElementById('live-clock').textContent = timeString;
                document.getElementById('live-date').textContent = dateString;

                let greeting = 'Good evening';
                if (hours >= 5 && hours < 12) {
                    greeting = 'Good morning';
                } else if (hours >= 12 && hours < 18) {
                    greeting = 'Good afternoon';
                }

                document.getElementById('greeting-heading').textContent = `${greeting}, ${userGreetingName}`;
            }

            updateClockAndGreeting();
            setInterval(updateClockAndGreeting, 1000);

            // Weather Fetch via Open-Meteo for Mandaluyong City
            async function fetchWeather() {
                try {
                    const response = await fetch('https://api.open-meteo.com/v1/forecast?latitude=14.5792&longitude=121.0359&current_weather=true');
                    if (!response.ok) throw new Error('Weather request failed');
                    
                    const data = await response.json();
                    const temp = Math.round(data.current_weather.temperature);
                    const code = data.current_weather.weathercode;

                    document.getElementById('weather-temp').textContent = `${temp}°C`;

                    let condition = 'Mandaluyong City';
                    if (code === 0) condition = 'Clear Sky';
                    else if ([1, 2, 3].includes(code)) condition = 'Partly Cloudy';
                    else if ([45, 48].includes(code)) condition = 'Foggy';
                    else if ([51, 53, 55, 61, 63, 65, 80, 81].includes(code)) condition = 'Rain Showers';
                    else if ([95, 96, 99].includes(code)) condition = 'Thunderstorm';

                    document.getElementById('weather-condition').textContent = condition;
                } catch (error) {
                    document.getElementById('weather-temp').textContent = '29°C';
                    document.getElementById('weather-condition').textContent = 'Mandaluyong City';
                }
            }

            fetchWeather();
        });
    </script>
    
    <!-- Vite Asset / Reverb & Echo Listener Integration -->
    @vite(['resources/js/app.js'])
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Echo !== 'undefined') {
                window.Echo.channel('dashboard-events')
                    .listen('.schedule.updated', (e) => {
                        console.log('Real-time schedule update triggered via Reverb:', e.eventData);
                        window.location.reload(); 
                    })
                    .listen('.system.updated', (e) => {
                        console.log('System launcher grid updated via Reverb:', e.systemData);
                        window.location.reload();
                    });
            }
        });
    </script>
</body>
</html>