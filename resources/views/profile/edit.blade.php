<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50/60">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Profile - LODISv2</title>

    <link rel="icon" type="image/png" href="{{ asset('images/LODISv2.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <meta name="theme-color" content="#00687A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LODISv2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50/60 text-slate-900 selection:bg-brand selection:text-white">

    <div id="app" class="flex-1 flex flex-col">
        @include('partials.header')

        <main id="app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <profile-view
                :user="{{ json_encode($user) }}"
                :employee="{{ json_encode($employeeData) }}"
                :pending-profile-request="{{ json_encode($pendingRequestData) }}"
                :status-colors="{{ json_encode($statusColors ?? []) }}"
                :security-settings="{{ json_encode($securitySettings ?? []) }}"
                :password-histories="{{ json_encode($passwordHistories) }}"
                :trusted-devices="{{ json_encode($trustedDevicesFormatted) }}"
                current-device-key="{{ request()->cookie('lodis_device_key') ?? '' }}"
                :user-systems="{{ json_encode($userSystemsFormatted) }}"
                :audit-logs="{{ json_encode($auditLogsFormatted) }}"
                status-message="{{ session('status') ?? '' }}"
                success-message="{{ session('success') ?? '' }}"
                :errors-list="{{ json_encode($errors->updatePassword->any() ? $errors->updatePassword->all() : ($errors->any() ? $errors->all() : [])) }}"
                csrf-token="{{ csrf_token() }}"
                banner-url="{{ asset('images/banner.png') }}"
                :routes="{{ json_encode($routes) }}"
            ></profile-view>
        </main>
    </div>

    @include('partials.footer')
    
    @include('partials.it-support-widget')

</body>
</html>