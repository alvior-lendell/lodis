<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Notification Center - LODISv2</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LODISv2.png') }}">
    <meta name="theme-color" content="#00687A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LODISv2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 flex flex-col">

    <div id="app" class="flex-1 flex flex-col">
        @include('partials.header')

        <main id="app" class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <notification-center-view
                :notifications="{{ json_encode($formattedNotifications) }}"
                :total-count="{{ $totalCount }}"
                :unread-count="{{ $unreadCount }}"
                current-filter="{{ $currentFilter }}"
                :filter-urls="{{ json_encode($filterUrls) }}"
                mark-all-read-url="{{ Route::has('notifications.markAllRead') ? route('notifications.markAllRead') : '' }}"
                csrf-token="{{ csrf_token() }}"
                status-message="{{ session('status') ?? '' }}"
                :pagination-html="{{ json_encode($notifications->links()->toHtml()) }}"
            ></notification-center-view>
        </main>
    </div>

    @include('partials.footer')
    
    @include('partials.it-support-widget')

</body>
</html>