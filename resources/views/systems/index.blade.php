<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Management - LODISv2</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <meta name="theme-color" content="#00687A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#00687A',
                            hover: '#00505E',
                            light: '#E6F0F2',
                        }
                    },
                    fontFamily: {
                        sans: ['"Century Gothic"', 'CenturyGothic', 'AppleGothic', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-900 flex flex-col">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center hover:opacity-90 transition">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-10 w-auto object-contain">
                </a>
                <span class="text-xs font-semibold text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">System Management</span>
            </div>

            <a href="{{ route('dashboard') }}" 
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </header>

    <!-- Main Workspace -->
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

        <!-- Action Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Registered Systems</h2>
                <p class="text-xs text-slate-500 mt-1">Configure operational platform launchers and manage dynamic portal visibility.</p>
            </div>

            <a href="{{ route('systems.create') }}" 
                class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add New System</span>
            </a>
        </div>

        <!-- Systems Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-6">Order</th>
                            <th class="py-3.5 px-6">Platform Logo</th>
                            <th class="py-3.5 px-6">System Info</th>
                            <th class="py-3.5 px-6">Target URL</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse ($systems as $system)
                            @php
                                $logoPath = $system->logo ? (str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo) : null;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 font-bold text-slate-900">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200">
                                        {{ $system->default_sort_order }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($logoPath && file_exists(public_path($logoPath)))
                                        <img src="{{ asset($logoPath) }}" alt="{{ $system->name }}" class="h-8 max-w-[100px] object-contain">
                                    @else
                                        <span class="text-[10px] italic text-slate-400">No Image</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm">{{ $system->name }}</div>
                                    @if ($system->description)
                                        <div class="text-slate-500 text-[11px] truncate max-w-xs mt-0.5">{{ $system->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 font-mono text-[11px] text-brand hover:underline">
                                    <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer">{{ $system->url }}</a>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($system->is_active)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Disabled</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('systems.edit', $system) }}" 
                                            class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition border border-slate-200" title="Edit System">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('systems.destroy', $system) }}" onsubmit="return confirm('Delete this system entry completely?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition border border-rose-200" title="Delete System">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500 font-semibold">
                                    No systems configured in database. Click "Add New System" to register one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>