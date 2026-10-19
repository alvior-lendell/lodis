<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Register User - LODISv2</title>

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
    <div id="app" class="flex-1 flex flex-col">
        @include('partials.header')

        <!-- Form Content -->
        <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="app">
                <user-create-view
                    :employees='@json($employeesFormatted)'
                    :old-values='@json($oldValues)'
                    :systems='@json($systemsFormatted)'
                    :is-superadmin='@json(auth()->user()->role === "Superadmin")'
                    :errors='@json($errors->toArray())'
                    :errors-list='@json($errors->all())'
                    csrf-token="{{ csrf_token() }}"
                    :routes='@json($routes)'
                ></user-create-view>
            </div>
        </main>

    </div>

        @include('partials.footer')
    
        @include('partials.it-support-widget')

</body>
</html>