<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Waiting for Authorization — LODISv2</title>

    <link rel="icon" type="image/png" href="{{ asset('images/LODISv2.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900 min-h-screen flex items-center justify-center p-6">

    <div id="app">
        <waiting-authorization-view
            :auth-id='@json($auth->id)'
            :expires-at='@json($auth->expires_at?->toIso8601String())'
            :has-trusted-devices='@json((bool) $hasTrustedDevices)'
            :has-sms='@json((bool) $hasSms)'
            :has-authenticator='@json((bool) $hasAuthenticator)'
            error-message="{{ $errors->any() ? $errors->first() : '' }}"
            csrf-token="{{ csrf_token() }}"
            :routes='@json($routes)'
        ></waiting-authorization-view>
    </div>

</body>
</html>