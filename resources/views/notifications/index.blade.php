<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token for Client-Side Fetch Requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Notification Center - LODISv2</title>

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
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

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

        <!-- Page Title & Global Actions Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Notification Center</h1>
                <p class="text-xs text-slate-500 mt-1">Manage system notifications, real-time security alerts, and event updates.</p>
            </div>

            @php
                $unreadCount = auth()->user()->unreadNotifications()->count();
            @endphp

            @if ($unreadCount > 0 && Route::has('notifications.markAllRead'))
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Mark All as Read</span>
                    </button>
                </form>
            @endif
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6 border-b border-slate-200 flex items-center gap-6 text-xs font-semibold">
            @php $currentFilter = request('filter', 'all'); @endphp
            
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
               class="pb-3 border-b-2 transition flex items-center gap-2 {{ $currentFilter === 'all' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <span>All Notifications</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-700 font-bold">
                    {{ auth()->user()->notifications()->count() }}
                </span>
            </a>

            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
               class="pb-3 border-b-2 transition flex items-center gap-2 {{ $currentFilter === 'unread' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <span>Unread Only</span>
                @if ($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-brand text-white font-bold">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Notification Cards Wrapper -->
        <div class="space-y-3">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $notifType = $notification->data['type'] ?? 'info';
                    $url = $notification->data['url'] ?? '#';
                @endphp

                <div class="bg-white rounded-2xl border transition duration-200 p-4 sm:p-5 flex items-start gap-4 shadow-sm hover:shadow-md relative overflow-hidden
                    {{ $isUnread ? 'border-brand/40 bg-slate-50/50' : 'border-slate-200' }}">
                    
                    @if ($isUnread)
                        <span class="absolute left-0 top-0 bottom-0 w-1 bg-brand"></span>
                    @endif

                    <!-- Category Icon -->
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center font-bold text-sm mt-0.5
                        {{ $notifType === 'security_alert' ? 'bg-amber-100 text-amber-700' : 'bg-brand-light text-brand' }}">
                        @if ($notifType === 'security_alert')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Details Body -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ $url }}" class="text-xs sm:text-sm font-extrabold text-slate-900 hover:text-brand transition truncate">
                                {{ $notification->data['title'] ?? 'System Update' }}
                            </a>
                            <span class="text-[11px] text-slate-400 shrink-0 font-mono">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $notification->data['message'] ?? 'You have a new system alert.' }}
                        </p>

                        @if (isset($notification->data['ip_address']))
                            <div class="mt-2 text-[10px] text-slate-500 font-mono flex items-center gap-3">
                                <span>IP: {{ $notification->data['ip_address'] }}</span>
                                <span>Platform: {{ $notification->data['platform'] ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Item Actions (Mark Single Read & Delete) -->
                    <div class="flex items-center gap-1 shrink-0 self-center">
                        @if ($isUnread && Route::has('notifications.markRead'))
                            <form method="POST" action="{{ route('notifications.markRead', $notification->id) }}">
                                @csrf
                                <button type="submit" title="Mark as read" class="p-2 rounded-lg text-slate-400 hover:text-brand hover:bg-slate-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                        @endif

                        @if (Route::has('notifications.destroy'))
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete notification" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-sm font-bold text-slate-800">No notifications found</h3>
                    <p class="text-xs text-slate-500 mt-1">You are all caught up on system updates and security alerts.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </main>

    <!-- Universal Footer Include -->
    @include('partials.footer')

</body>
</html>