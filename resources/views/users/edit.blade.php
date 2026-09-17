<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token for Client-Side Fetch Requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Edit User - LODISv2</title>

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

    <!-- Form Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Account Configuration</h2>
            <p class="text-xs text-slate-500 mt-1 mb-6">Modify account details, adjust RBAC permissions, or reset credentials.</p>

            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Employee ID & Role Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="employee_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono">
                        @error('employee_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">System Role *</label>
                        <select name="role" id="role" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition bg-white">
                            <option value="Employee" {{ old('role', $user->role) === 'Employee' ? 'selected' : '' }}>Employee</option>
                            <option value="Admin" {{ old('role', $user->role) === 'Admin' ? 'selected' : '' }}>Admin</option>
                            @if (auth()->user()->role === 'Superadmin')
                                <option value="Superadmin" {{ old('role', $user->role) === 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                            @endif
                        </select>
                        @error('role') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition">
                    @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono">
                    @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Reset Password Block -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Reset Account Password</h4>
                        <p class="text-[11px] text-slate-500">Leave password fields blank if you do not want to alter current credentials.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition bg-white"
                                placeholder="New password">
                            @error('password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition bg-white"
                                placeholder="Confirm new password">
                        </div>
                    </div>
                </div>

                <!-- Active Status -->
                <div>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer">
                        <span class="text-xs font-bold text-slate-800">Account Active (Permit Login)</span>
                    </label>
                </div>

                <!-- System Access Grants Section -->
                @php
                    $userSystems = $user->systems->keyBy('id');
                @endphp

                <div class="pt-6 border-t border-slate-200">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-slate-900">System Access Grants</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Specify platform access permissions and subsystem roles for this user account.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($systems as $system)
                            @php
                                $logoPath = $system->logo ? (str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo) : null;
                                $existingGrant = $userSystems->get($system->id);
                                $hasAccess = old("systems.{$system->id}.has_access", $existingGrant?->pivot->has_access ?? false);
                                $currentRole = old("systems.{$system->id}.role", $existingGrant?->pivot->role ?? 'Employee');
                            @endphp
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        @if ($logoPath && file_exists(public_path($logoPath)))
                                            <img src="{{ asset($logoPath) }}" alt="{{ $system->name }}" class="h-6 w-auto object-contain">
                                        @else
                                            <span class="font-bold text-xs text-slate-800">{{ $system->name }}</span>
                                        @endif
                                    </div>

                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="systems[{{ $system->id }}][has_access]" value="1" {{ $hasAccess ? 'checked' : '' }}
                                            class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand">
                                        <span class="text-xs font-bold text-slate-700">Grant Access</span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-200/60">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase">Subsystem Role:</span>
                                    <select name="systems[{{ $system->id }}][role]" 
                                        class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 bg-white outline-none focus:border-brand">
                                        <option value="Employee" {{ $currentRole === 'Employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="Admin" {{ $currentRole === 'Admin' ? 'selected' : '' }}>Admin</option>
                                        @if (auth()->user()->role === 'Superadmin')
                                            <option value="Superadmin" {{ $currentRole === 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                                        @endif
                                        <option value="None" {{ $currentRole === 'None' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic col-span-full">No active systems available to grant access.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-slate-100 pt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" 
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm">
                        Update User Account
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>