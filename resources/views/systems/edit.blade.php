<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit System - LODISv2</title>

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
                <a href="{{ route('systems.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition">System Management</a>
                <span class="text-xs font-semibold text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Edit System</span>
            </div>

            <a href="{{ route('systems.index') }}" 
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Cancel</span>
            </a>
        </div>
    </header>

    <!-- Form Content -->
    <main class="flex-1 max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Application Entry</h2>
            <p class="text-xs text-slate-500 mt-1 mb-6">Modify platform URL, order, logo graphic, or launcher access visibility.</p>

            <form method="POST" action="{{ route('systems.update', $system) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name Input -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">System Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $system->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition">
                    @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- URL Input with Constant https:// prefix -->
                <div>
                    <label for="url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Target Launch URL *</label>
                    <div class="flex rounded-xl border border-slate-200 overflow-hidden focus-within:border-brand focus-within:ring-1 focus-within:ring-brand shadow-sm transition">
                        <span class="inline-flex items-center px-3.5 bg-slate-100 text-slate-500 font-mono text-xs font-bold border-r border-slate-200 select-none shrink-0">https://</span>
                        <input type="text" name="url" id="url" value="{{ preg_replace('/^https?:\/\//i', '', old('url', $system->url)) }}" required
                            class="w-full py-2.5 px-3 text-xs font-medium text-slate-900 outline-none font-mono">
                    </div>
                    @error('url') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Description Input -->
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition">{{ old('description', $system->description) }}</textarea>
                    @error('description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Sort Order & Active Status Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="default_sort_order" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sort Order *</label>
                        <input type="number" name="default_sort_order" id="default_sort_order" value="{{ old('default_sort_order', $system->default_sort_order) }}" min="0" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono">
                        @error('default_sort_order') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col justify-center">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Launcher Status</label>
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $system->is_active) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer">
                            <span class="text-xs font-bold text-slate-800">Enable Launcher Tile</span>
                        </label>
                    </div>
                </div>

                <!-- Logo Image Upload & Current Image Display -->
                @php
                    $logoPath = $system->logo ? (str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo) : null;
                    $hasLogo = $logoPath && file_exists(public_path($logoPath));
                @endphp

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Platform Logo Asset</label>
                    <div class="flex items-center gap-4">
                        <div id="logo-preview-box" class="w-24 h-20 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden p-2">
                            <img id="logo-preview-img" src="{{ $hasLogo ? asset($logoPath) : '#' }}" alt="Current Logo" class="{{ $hasLogo ? '' : 'hidden' }} max-h-16 w-auto object-contain">
                            <span class="text-[10px] text-slate-400 font-semibold text-center {{ $hasLogo ? 'hidden' : '' }}" id="logo-preview-text">No Logo</span>
                        </div>

                        <div class="flex-1">
                            <input type="file" name="logo" id="logo-input" accept="image/*"
                                class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-light file:text-brand hover:file:bg-brand/20 cursor-pointer">
                            <span class="text-[11px] text-slate-400 mt-1 block">Upload a new file to replace current asset.</span>
                        </div>
                    </div>
                    @error('logo') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Form Action Buttons -->
                <div class="border-t border-slate-100 pt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('systems.index') }}" 
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm">
                        Update System Entry
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('logo-input')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const img = document.getElementById('logo-preview-img');
            const txt = document.getElementById('logo-preview-text');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    if (txt) txt.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>