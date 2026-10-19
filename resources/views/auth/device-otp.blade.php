<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Authorize Device — LODISv2</title>

    <link rel="icon" type="image/png" href="{{ asset('images/LODISv2.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900 min-h-screen flex items-center justify-center p-6">

    <div id="app" class="w-full max-w-md flex justify-center">
        <authorize-device-view
            method="{{ $method ?? 'email' }}"
            email="{{ $email ?? '' }}"
            :expires-at='@json(($expiresAt ?? null) ? \Carbon\Carbon::parse($expiresAt)->toIso8601String() : null)'
            :has-sms='@json((bool) ($hasSms ?? false))'
            :has-authenticator='@json((bool) ($hasAuthenticator ?? false))'
            :cooldown-seconds='@json($cooldownSeconds ?? 0)'
            status-message="{{ session('status') ?? '' }}"
            error-message="{{ $errors->any() ? $errors->first() : '' }}"
            :auth-id='@json($authId ?? null)'
            csrf-token="{{ csrf_token() }}"
            :routes='@json($routes)'
        ></authorize-device-view>
    </div>

</body>
</html>