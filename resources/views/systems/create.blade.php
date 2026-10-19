<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add System - LODISv2</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <meta name="theme-color" content="#00687A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 flex flex-col">

    <div id="app" class="flex-1 flex flex-col">
        @include('partials.header')

        <main class="flex-1 max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="app">
                <system-create-view
                    :old-values='@json($oldValues)'
                    :next-sort-order='@json($nextSortOrder)'
                    :errors='@json($errors->toArray())'
                    :errors-list='@json($errors->all())'
                    csrf-token="{{ csrf_token() }}"
                    :routes='@json($routes)'
                ></system-create-view>
            </div>
        </main>

    </div>

    @include('partials.footer')

    @include('partials.it-support-widget')

</body>
</html>