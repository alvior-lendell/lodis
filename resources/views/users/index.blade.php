<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>User Management - LODISv2</title>

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

    @include('partials.header')

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

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

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">System User Directory</h2>
                <p class="text-xs text-slate-500 mt-1">Manage employee access privileges, operational roles, and security credentials.</p>
            </div>

            <a href="{{ route('users.create') }}" 
                class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Register New User</span>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="w-full sm:flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search by name, email, or employee ID..." 
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition">
                </div>

                <div class="w-full sm:w-48">
                    <select name="role" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition bg-white">
                        <option value="">All Roles</option>
                        @if (auth()->user()->isSuperadmin())
                            <option value="Superadmin" {{ request('role') === 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                        @endif
                        <option value="Admin" {{ request('role') === 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Employee" {{ request('role') === 'Employee' ? 'selected' : '' }}>Employee</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'role']))
                        <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-6">Employee ID</th>
                            <th class="py-3.5 px-6">Name & Email</th>
                            <th class="py-3.5 px-6">Role</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6">Last Password Change</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    {{ $user->employee_id ?: '—' }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm">{{ $user->name }}</div>
                                    <div class="text-slate-500 text-[11px] font-mono">{{ $user->email }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->role === 'Superadmin')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Superadmin</span>
                                    @elseif ($user->role === 'Admin')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Admin</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Employee</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->is_active)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Disabled</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-slate-500 font-mono text-[11px]">
                                    {{ $user->password_changed_at ? \Carbon\Carbon::parse($user->password_changed_at)->format('Y-m-d H:i') : 'Never' }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}" 
                                            class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition border border-slate-200" title="Edit User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        @if (auth()->user()->isSuperadmin() && auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user account completely?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition border border-rose-200" title="Delete User">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500 font-semibold">
                                    No user accounts matched your search parameters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>