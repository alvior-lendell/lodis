<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50/60">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token for Client-Side Fetch Requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Profile - LODISv2</title>

    <!-- Favicon & PWA Directives -->
    <link rel="icon" type="image/png" href="{{ asset('images/LODISv2.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
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
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50/60 text-slate-900 selection:bg-brand selection:text-white">

    <!-- Shared Header Include -->
    @include('partials.header')

    <main id="auth-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Status Alerts -->
        @if (session('status') || session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium leading-relaxed">{{ session('status') ?? session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-bold text-rose-800">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Please resolve the following submission errors:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 font-medium pl-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Identity Banner Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden relative">
            <div class="h-40 bg-gradient-to-r from-brand via-brand-hover to-brand-accent relative overflow-hidden">
                <div class="absolute -top-16 -left-16 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -right-16 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            </div>

            <div class="px-6 sm:px-8 pb-6 flex flex-col md:flex-row items-center md:items-end justify-between gap-6 -mt-16 relative z-10">
                <div class="flex flex-col sm:flex-row items-center sm:items-end gap-5 text-center sm:text-left">
                    <div class="relative group shrink-0">
                        @if ($employee?->avatar_url)
                            <img id="hero-avatar-img" src="{{ $employee->avatar_url }}" data-original-src="{{ $employee->avatar_url }}" alt="{{ $employee->full_name }}"
                                class="w-28 h-28 rounded-3xl object-cover border-4 border-white shadow-md shadow-slate-900/10 transition group-hover:brightness-90">
                            <div id="hero-initials-fallback" class="hidden w-28 h-28 rounded-3xl bg-slate-900 text-white font-extrabold text-3xl flex items-center justify-center border-4 border-white shadow-md shadow-slate-900/10 tracking-widest uppercase">
                                {{ strtoupper(substr($employee->first_name ?: $employee->full_name, 0, 1) . substr($employee->last_name ?: $employee->full_name, 0, 1)) }}
                            </div>
                        @else
                            <div id="hero-initials-fallback" class="w-28 h-28 rounded-3xl bg-slate-900 text-white font-extrabold text-3xl flex items-center justify-center border-4 border-white shadow-md shadow-slate-900/10 tracking-widest uppercase">
                                {{ strtoupper(substr($employee?->first_name ?: ($user->name ?? 'EU'), 0, 1) . substr($employee?->last_name ?: ($user->name ?? 'EU'), 0, 1)) }}
                            </div>
                            <img id="hero-avatar-img" src="" alt="{{ $employee?->full_name ?? $user->name }}"
                                class="hidden w-28 h-28 rounded-3xl object-cover border-4 border-white shadow-md shadow-slate-900/10">
                        @endif
                        <button type="button" onclick="document.getElementById('profile_photo').click()"
                            class="absolute inset-0 bg-slate-900/60 rounded-3xl opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center text-white text-[10px] font-bold gap-1 border-4 border-white backdrop-blur-[2px]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Change</span>
                        </button>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $employee?->full_name ?? $user->name }}</h1>
                        </div>
                        <p class="text-xs font-semibold text-slate-500 font-mono">{{ $employee?->email ?? $user->email }}</p>
                        <p class="text-xs text-slate-400 font-medium">
                            {{ $employee?->position ?? 'Staff Member' }} • <span class="text-slate-700 font-bold">{{ $employee?->department ?? 'General Operations' }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap justify-center">
                    <span class="px-4 py-1.5 rounded-2xl text-xs font-bold border {{ $statusColors[$employee?->employment_status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }} flex items-center gap-2 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                        Status: {{ $employee?->employment_status ?? 'Probationary' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Workspace Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Sidebar Navigation Tabs -->
            <aside class="lg:col-span-3 bg-white rounded-3xl border border-slate-200/80 p-3 shadow-sm sticky top-24">
                <nav class="flex lg:flex-col overflow-x-auto no-scrollbar gap-1" aria-label="Tabs">
                    <button type="button" data-tab="tab-overview" class="tab-btn w-full py-3.5 px-4 rounded-2xl font-bold text-xs text-brand bg-brand-light/60 transition whitespace-nowrap flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Employee Details</span>
                    </button>
                    <button type="button" data-tab="tab-security" class="tab-btn w-full py-3.5 px-4 rounded-2xl font-bold text-xs text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition whitespace-nowrap flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Password Security</span>
                    </button>
                    <button type="button" data-tab="tab-sessions" class="tab-btn w-full py-3.5 px-4 rounded-2xl font-bold text-xs text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition whitespace-nowrap flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Active Sessions</span>
                    </button>
                    <button type="button" data-tab="tab-subsystems" class="tab-btn w-full py-3.5 px-4 rounded-2xl font-bold text-xs text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition whitespace-nowrap flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Subsystem Access</span>
                    </button>
                    <button type="button" data-tab="tab-audit" class="tab-btn w-full py-3.5 px-4 rounded-2xl font-bold text-xs text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition whitespace-nowrap flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Audit History</span>
                    </button>
                </nav>
            </aside>

            <!-- Main Content Panels -->
            <div class="lg:col-span-9 space-y-6">

                <!-- Tab 1: Employee Details -->
                <div id="tab-overview" class="tab-content space-y-6">

                    <!-- Quick Facts Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Employee ID</span>
                            <span class="text-base font-extrabold text-slate-800 font-mono block tracking-tight">{{ $employee?->employee_id ?? 'EMP-LOCAL' }}</span>
                        </div>
                        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Official Position</span>
                            <span class="text-base font-bold text-slate-800 block truncate tracking-tight">{{ $employee?->position ?? 'Staff Member' }}</span>
                        </div>
                        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Assigned Department</span>
                            <span class="text-base font-bold text-slate-800 block truncate tracking-tight">{{ $employee?->department ?? 'General Operations' }}</span>
                        </div>
                    </div>

                    <!-- Avatar Card -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Profile Photo</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Avatar updates apply instantly. Accepted formats: JPG, PNG, WEBP (Max: 2 MB).</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200/80 w-fit">
                                Direct Upload
                            </span>
                        </div>

                        <form id="avatar-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="flex flex-col sm:flex-row items-center gap-6 p-6 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                                <div class="relative shrink-0">
                                    <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-slate-200 bg-white flex items-center justify-center relative shadow-sm">
                                        @if ($employee?->avatar_url)
                                            <img id="avatar-preview-img" src="{{ $employee->avatar_url }}" data-original-src="{{ $employee->avatar_url }}" alt="Avatar Preview" class="w-full h-full object-cover">
                                            <div id="avatar-initials-fallback" class="hidden w-full h-full bg-slate-900 text-white font-extrabold text-2xl flex items-center justify-center uppercase tracking-wider">
                                                {{ strtoupper(substr($employee->first_name ?: $employee->full_name, 0, 1) . substr($employee->last_name ?: $employee->full_name, 0, 1)) }}
                                            </div>
                                        @else
                                            <div id="avatar-initials-fallback" class="w-full h-full bg-slate-900 text-white font-extrabold text-2xl flex items-center justify-center uppercase tracking-wider">
                                                {{ strtoupper(substr($employee?->first_name ?: ($user->name ?? 'EU'), 0, 1) . substr($employee?->last_name ?: ($user->name ?? 'EU'), 0, 1)) }}
                                            </div>
                                            <img id="avatar-preview-img" src="" alt="Avatar Preview" class="hidden w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <span id="preview-badge" class="hidden absolute -top-2 -right-2 bg-brand text-white text-[9px] font-extrabold px-2.5 py-0.5 rounded-full shadow-sm">Preview</span>
                                </div>

                                <div class="flex-1 space-y-3 text-center sm:text-left w-full">
                                    <div>
                                        <label for="profile_photo" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 hover:border-slate-300 cursor-pointer transition shadow-sm">
                                            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span id="file-select-label">Choose Image File</span>
                                        </label>
                                        <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="hidden">
                                    </div>

                                    <div id="avatar-file-info" class="text-xs font-medium text-slate-500 min-h-[18px]">
                                        Allowed formats: JPG, PNG, WEBP (Max 2MB).
                                    </div>

                                    <div id="avatar-action-controls" class="hidden flex items-center gap-3 pt-1 justify-center sm:justify-start">
                                        <button id="upload-avatar-btn" type="submit" class="px-5 py-2.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Save Avatar</span>
                                        </button>
                                        <button id="cancel-avatar-btn" type="button" class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold transition">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Personal Information Form Card -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Personal Profile Request</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Modifications to official employee records are submitted for HR verification.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200/80 w-fit">
                                HR Approval Required
                            </span>
                        </div>

                        <form method="POST" action="{{ route('profile.update') }}" autocomplete="off" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            <!-- Name Fields Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">First Name <span class="text-rose-500">*</span></label>
                                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $employee?->first_name) }}" required
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>

                                <div>
                                    <label for="middle_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Middle Name</label>
                                    <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $employee?->middle_name) }}"
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>

                                <div>
                                    <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Last Name <span class="text-rose-500">*</span></label>
                                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $employee?->last_name) }}" required
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>

                                <div>
                                    <label for="suffix" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Suffix</label>
                                    <input id="suffix" name="suffix" type="text" value="{{ old('suffix', $employee?->suffix) }}" placeholder="Jr., III"
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>
                            </div>

                            <!-- Contact & Gender Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="contact_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Contact Number</label>
                                    <input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number', $employee?->contact_number) }}" placeholder="+63 912 345 6789"
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>

                                <div>
                                    <label for="gender" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Gender</label>
                                    <select id="gender" name="gender" class="form-select block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                        <option value="" {{ empty($employee?->gender) ? 'selected' : '' }}>Select Gender</option>
                                        <option value="Male" {{ old('gender', $employee?->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $employee?->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Non-Binary" {{ old('gender', $employee?->gender) === 'Non-Binary' ? 'selected' : '' }}>Non-Binary</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Birthdate & Address Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="birthdate" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Birth Date</label>
                                    <input id="birthdate" name="birthdate" type="date" value="{{ old('birthdate', $employee?->raw_birthdate) }}"
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>

                                <div>
                                    <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Residential Address</label>
                                    <input id="address" name="address" type="text" value="{{ old('address', $employee?->address) }}" placeholder="Street, City, Province"
                                        class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition">
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-2 border-t border-slate-100">
                                <button type="submit" class="px-6 py-3.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Submit Request to HR</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Tab 2: Password Security -->
                <div id="tab-security" class="tab-content hidden space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Rotate Account Password</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Maintain security by ensuring your password satisfies system requirements.</p>
                        </div>

                        @if (Route::has('password.update'))
                            <form id="password-update-form" method="POST" action="{{ route('password.update') }}" autocomplete="off" class="space-y-5 max-w-xl">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Current Password</label>
                                    <div class="relative rounded-2xl shadow-sm">
                                        <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                                            class="form-input block w-full pl-4 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition"
                                            placeholder="••••••••">
                                        <button type="button" id="toggle-current-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password</label>
                                    <div class="relative rounded-2xl shadow-sm">
                                        <input id="password" type="password" name="password" required autocomplete="new-password"
                                            class="form-input block w-full pl-4 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition"
                                            placeholder="••••••••">
                                        <button type="button" id="toggle-new-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm New Password</label>
                                    <div class="relative rounded-2xl shadow-sm">
                                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                            class="form-input block w-full pl-4 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition"
                                            placeholder="••••••••">
                                        <button type="button" id="toggle-confirm-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-2.5">
                                    <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">Security Policy Requirements</span>
                                    
                                    <div id="rule-length" class="flex items-center gap-2 text-slate-400 transition">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>Minimum {{ $securitySettings?->min_password_length ?? 8 }} characters</span>
                                    </div>

                                    @if ($securitySettings?->require_uppercase)
                                        <div id="rule-uppercase" class="flex items-center gap-2 text-slate-400 transition">
                                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span>At least one uppercase character (A-Z)</span>
                                        </div>
                                    @endif

                                    @if ($securitySettings?->require_numeric)
                                        <div id="rule-numeric" class="flex items-center gap-2 text-slate-400 transition">
                                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span>At least one numeric digit (0-9)</span>
                                        </div>
                                    @endif

                                    @if ($securitySettings?->require_special_char)
                                        <div id="rule-special" class="flex items-center gap-2 text-slate-400 transition">
                                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span>At least one special character (!@#$%^&*)</span>
                                        </div>
                                    @endif

                                    <div id="rule-match" class="flex items-center gap-2 text-slate-400 transition">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>Passwords match</span>
                                    </div>
                                </div>

                                <button id="update-password-btn" type="submit" disabled
                                    class="px-6 py-3.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg id="pwd-spinner" class="hidden animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span id="pwd-label">Update Password</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Tab 3: Active Sessions & Trusted Devices -->
                <div id="tab-sessions" class="tab-content hidden space-y-6">
                
                    <!-- Active SSO Sessions Card -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Active SSO Sessions</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Manage devices currently logged into your LODISv2 profile.</p>
                            </div>
                
                            @if (Route::has('sessions.revoke-others'))
                                <form method="POST" action="{{ route('sessions.revoke-others') }}" autocomplete="off">
                                    @csrf
                                    <button type="submit" class="px-4 py-2.5 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 text-xs font-bold transition flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Revoke Other Sessions</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                
                        @php
                            $dbSessions = $sessions ?? \Illuminate\Support\Facades\DB::table('sessions')
                                ->where('user_id', auth()->id())
                                ->orderBy('last_activity', 'desc')
                                ->get();
                
                            $parseAgent = function ($ua) {
                                $platform = 'Unknown OS';
                                $browser = 'Unknown Browser';
                                $isDesktop = true;
                
                                if (preg_match('/Windows/i', $ua)) $platform = 'Windows PC';
                                elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) $platform = 'macOS';
                                elseif (preg_match('/Linux/i', $ua)) $platform = 'Linux';
                                elseif (preg_match('/Android/i', $ua)) { $platform = 'Android'; $isDesktop = false; }
                                elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) { $platform = 'iOS'; $isDesktop = false; }
                
                                if (preg_match('/Edg/i', $ua)) $browser = 'Microsoft Edge';
                                elseif (preg_match('/Chrome/i', $ua)) $browser = 'Google Chrome';
                                elseif (preg_match('/Firefox/i', $ua)) $browser = 'Mozilla Firefox';
                                elseif (preg_match('/Safari/i', $ua)) $browser = 'Apple Safari';
                                elseif (preg_match('/Opera|OPR/i', $ua)) $browser = 'Opera';
                
                                return (object) [
                                    'platform' => $platform,
                                    'browser' => $browser,
                                    'isDesktop' => $isDesktop
                                ];
                            };
                        @endphp
                
                        <div class="border border-slate-200/80 rounded-2xl overflow-hidden divide-y divide-slate-100">
                            @forelse ($dbSessions as $sess)
                                @php
                                    $uaInfo = $parseAgent($sess->user_agent ?? '');
                                    $isCurrent = ($sess->id === request()->session()->getId());
                                @endphp
                                <div class="p-5 flex items-center justify-between gap-4 bg-white hover:bg-slate-50/50 transition">
                                    <div class="flex items-center gap-4">
                                        <div class="p-3 rounded-2xl {{ $isCurrent ? 'bg-brand-light text-brand' : 'bg-slate-100 text-slate-500' }} shrink-0">
                                            @if ($uaInfo->isDesktop)
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            @else
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs font-bold text-slate-900">{{ $uaInfo->platform }} — {{ $uaInfo->browser }}</h4>
                                                @if ($isCurrent)
                                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase tracking-wider">This Session</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                IP: {{ $sess->ip_address ?? '127.0.0.1' }} • <span class="text-slate-400">Last active {{ \Carbon\Carbon::createFromTimestamp($sess->last_activity)->diffForHumans() }}</span>
                                            </p>
                                        </div>
                                    </div>
                
                                    @if (!$isCurrent && Route::has('sessions.revoke'))
                                        <form method="POST" action="{{ route('sessions.revoke', $sess->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold border border-slate-200 hover:border-rose-200 transition">
                                                Logout
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="p-8 text-center bg-slate-50 space-y-2">
                                    <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <h4 class="text-xs font-bold text-slate-700">No Active Database Sessions Found</h4>
                                    <p class="text-[11px] text-slate-500">Ensure `SESSION_DRIVER=database` is configured in your setup.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                
                    <!-- Recognized & Trusted Devices Card -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Recognized & Trusted Devices</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Registered devices authorized to access your profile without triggering security warnings.</p>
                            </div>
                        </div>
                
                        @php
                            $trustedDevices = auth()->user()->userDevices()->orderBy('last_active_at', 'desc')->get() ?? collect();
                            $currentDeviceKey = request()->cookie('lodis_device_key');
                        @endphp
                
                        <div class="border border-slate-200/80 rounded-2xl overflow-hidden divide-y divide-slate-100">
                            @forelse ($trustedDevices as $dev)
                                <div class="p-5 flex items-center justify-between gap-4 bg-white hover:bg-slate-50/50 transition">
                                    <div class="flex items-center gap-4">
                                        <div class="p-3 rounded-2xl {{ $dev->device_key === $currentDeviceKey ? 'bg-brand-light text-brand' : 'bg-slate-100 text-slate-500' }} shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs font-bold text-slate-900">{{ $dev->device_name }}</h4>
                                                @if ($dev->device_key === $currentDeviceKey)
                                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase tracking-wider">This Device</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                IP: {{ $dev->ip_address }} • <span class="text-slate-400">Last active {{ $dev->last_active_at?->diffForHumans() }}</span>
                                            </p>
                                        </div>
                                    </div>
                
                                    @if ($dev->device_key !== $currentDeviceKey && Route::has('devices.revoke'))
                                        <form method="POST" action="{{ route('devices.revoke', $dev->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Remove this device from trusted list? Logins from this device will trigger security notifications.');" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold border border-slate-200 hover:border-rose-200 transition">
                                                Forget Device
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="p-8 text-center bg-slate-50 space-y-2">
                                    <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <h4 class="text-xs font-bold text-slate-700">No Trusted Devices Logged</h4>
                                    <p class="text-[11px] text-slate-500">Sign in from your standard workstation to register it as a trusted device.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                
                </div>

                <!-- Tab 4: Subsystem Access Grid -->
                <div id="tab-subsystems" class="tab-content hidden space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Ecosystem Subsystem Entitlements</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Application access privileges managed through system authorization policy.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse ($user->systems as $system)
                                @php
                                    $hasAccess = (bool) ($system->pivot->has_access ?? false);
                                    $role = $system->pivot->role ?? 'None';
                                    $customOrder = $system->pivot->custom_order ?? null;
                                @endphp
                                <div class="p-5 rounded-2xl border {{ $hasAccess ? 'border-slate-200/80 bg-white shadow-sm hover:border-brand/40' : 'border-slate-100 bg-slate-50/50 opacity-70' }} transition flex flex-col justify-between gap-4">
                                    <div class="space-y-2.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold font-mono tracking-wider {{ $hasAccess ? 'bg-brand-light text-brand' : 'bg-slate-200 text-slate-600' }}">
                                                {{ $system->code ?? $system->name }}
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                @if (!is_null($customOrder))
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono bg-slate-100 text-slate-500 border border-slate-200">
                                                        #{{ $customOrder }}
                                                    </span>
                                                @endif
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $hasAccess ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                                    {{ $hasAccess ? 'Authorized' : 'Unassigned' }}
                                                </span>
                                            </div>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ $system->name }}</h4>
                                    </div>

                                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                        <span>Entitlement Role:</span>
                                        <span class="font-bold text-slate-800">{{ $role }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full p-8 text-center bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2">
                                    <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <h4 class="text-xs font-bold text-slate-700">No Subsystem Mappings Configured</h4>
                                    <p class="text-[11px] text-slate-500 max-w-sm mx-auto">No subsystem access records found in system configuration.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Security Audit History -->
                <div id="tab-audit" class="tab-content hidden space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Security Audit History</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Automated event logging of profile views, authentication activity, and security changes.</p>
                        </div>
                
                        <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">Security Event</th>
                                        <th class="p-4">IP Address</th>
                                        <th class="p-4">Timestamp</th>
                                        <th class="p-4 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                                    @forelse ($auditLogs as $log)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="p-4 font-bold text-slate-900">
                                                @switch($log->event)
                                                    @case('login')
                                                        <span class="text-emerald-700 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                                            Account Signed In
                                                        </span>
                                                        @break
                
                                                    @case('logout')
                                                        <span class="text-slate-600 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                            Account Signed Out
                                                        </span>
                                                        @break
                
                                                    @case('new_device_login')
                                                        <span class="text-amber-700 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                            New Device Authorization
                                                        </span>
                                                        @break
                
                                                    @case('password_change')
                                                        <span class="text-blue-700 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/></svg>
                                                            Password Modified
                                                        </span>
                                                        @break
                
                                                    @case('password_reset')
                                                        <span class="text-purple-700 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            Password Reset Completed
                                                        </span>
                                                        @break
                
                                                    @case('password_expired')
                                                        <span class="text-rose-700 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Password Expiry Action Required
                                                        </span>
                                                        @break
                
                                                    @default
                                                        <span class="text-slate-700">{{ title_case(str_replace('_', ' ', $log->event)) }}</span>
                                                @endswitch
                                            </td>
                                            <td class="p-4 font-mono text-slate-500">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                            <td class="p-4 text-slate-500">{{ $log->created_at->format('M d, Y H:i:s') }} ({{ $log->created_at->diffForHumans() }})</td>
                                            <td class="p-4 text-right">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                                                    @if(in_array($log->event, ['login', 'password_change', 'password_reset'])) bg-emerald-100 text-emerald-800
                                                    @elseif($log->event === 'new_device_login') bg-amber-100 text-amber-800
                                                    @elseif($log->event === 'password_expired') bg-rose-100 text-rose-800
                                                    @else bg-slate-100 text-slate-700 @endif">
                                                    Logged
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-8 text-center text-slate-500 text-xs">No security audit logs recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Page Specific Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            const activateTab = (targetTabId) => {
                const targetBtn = document.querySelector(`.tab-btn[data-tab="${targetTabId}"]`);
                const targetContent = document.getElementById(targetTabId);

                if (!targetBtn || !targetContent) return;

                tabBtns.forEach(b => {
                    b.classList.remove('text-brand', 'bg-brand-light/60');
                    b.classList.add('text-slate-500', 'hover:bg-slate-50');
                });

                targetBtn.classList.remove('text-slate-500', 'hover:bg-slate-50');
                targetBtn.classList.add('text-brand', 'bg-brand-light/60');

                tabContents.forEach(content => content.classList.add('hidden'));
                targetContent.classList.remove('hidden');
            };

            const handleHashChange = () => {
                const hash = window.location.hash.replace('#', '').trim();
                if (!hash) return;

                const hashMapping = {
                    'overview': 'tab-overview',
                    'security': 'tab-security',
                    'password': 'tab-security',
                    'sessions': 'tab-sessions',
                    'subsystems': 'tab-subsystems',
                    'audit': 'tab-audit'
                };

                const targetTabId = hashMapping[hash] || (hash.startsWith('tab-') ? hash : `tab-${hash}`);
                activateTab(targetTabId);
            };

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetTabId = btn.getAttribute('data-tab');
                    const hashName = targetTabId.replace('tab-', '');
                    window.location.hash = hashName;
                    activateTab(targetTabId);
                });
            });

            handleHashChange();
            window.addEventListener('hashchange', handleHashChange);

            // Dual Avatar Preview Logic
            const photoInput = document.getElementById('profile_photo');
            const previewImg = document.getElementById('avatar-preview-img');
            const initialsFallback = document.getElementById('avatar-initials-fallback');
            const heroPreviewImg = document.getElementById('hero-avatar-img');
            const heroInitialsFallback = document.getElementById('hero-initials-fallback');
            const previewBadge = document.getElementById('preview-badge');
            const fileInfo = document.getElementById('avatar-file-info');
            const actionControls = document.getElementById('avatar-action-controls');
            const cancelBtn = document.getElementById('cancel-avatar-btn');
            const fileSelectLabel = document.getElementById('file-select-label');

            const MAX_FILE_SIZE = 2 * 1024 * 1024;

            const resetAvatarPreview = () => {
                if (!photoInput) return;
                photoInput.value = '';
                
                const originalSrc = previewImg?.getAttribute('data-original-src');
                if (originalSrc) {
                    if (previewImg) { previewImg.src = originalSrc; previewImg.classList.remove('hidden'); }
                    if (initialsFallback) initialsFallback.classList.add('hidden');
                } else {
                    if (previewImg) { previewImg.src = ''; previewImg.classList.add('hidden'); }
                    if (initialsFallback) initialsFallback.classList.remove('hidden');
                }

                const heroOriginalSrc = heroPreviewImg?.getAttribute('data-original-src');
                if (heroOriginalSrc) {
                    if (heroPreviewImg) { heroPreviewImg.src = heroOriginalSrc; heroPreviewImg.classList.remove('hidden'); }
                    if (heroInitialsFallback) heroInitialsFallback.classList.add('hidden');
                } else {
                    if (heroPreviewImg) { heroPreviewImg.src = ''; heroPreviewImg.classList.add('hidden'); }
                    if (heroInitialsFallback) heroInitialsFallback.classList.remove('hidden');
                }

                if (previewBadge) previewBadge.classList.add('hidden');
                if (actionControls) actionControls.classList.add('hidden');
                if (fileSelectLabel) fileSelectLabel.textContent = 'Choose Image File';
                if (fileInfo) {
                    fileInfo.className = 'text-xs font-medium text-slate-500';
                    fileInfo.textContent = 'Allowed formats: JPG, PNG, WEBP (Max 2MB).';
                }
            };

            photoInput?.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) { resetAvatarPreview(); return; }

                activateTab('tab-overview');
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

                if (file.size > MAX_FILE_SIZE) {
                    if (fileInfo) {
                        fileInfo.className = 'text-xs font-bold text-rose-600 flex items-center justify-center sm:justify-start gap-1 mt-1';
                        fileInfo.innerHTML = `<span>Selected file (${fileSizeMB} MB) exceeds maximum allowed size of 2 MB.</span>`;
                    }
                    if (actionControls) actionControls.classList.add('hidden');
                    if (previewBadge) previewBadge.classList.add('hidden');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    const result = event.target.result;
                    if (previewImg) { previewImg.src = result; previewImg.classList.remove('hidden'); }
                    if (initialsFallback) initialsFallback.classList.add('hidden');
                    if (heroPreviewImg) { heroPreviewImg.src = result; heroPreviewImg.classList.remove('hidden'); }
                    if (heroInitialsFallback) heroInitialsFallback.classList.add('hidden');
                    if (previewBadge) previewBadge.classList.remove('hidden');
                    if (actionControls) actionControls.classList.remove('hidden');
                    if (fileSelectLabel) fileSelectLabel.textContent = 'Change Selection';
                    if (fileInfo) {
                        fileInfo.className = 'text-xs font-semibold text-emerald-600 flex items-center justify-center sm:justify-start gap-1 mt-1';
                        fileInfo.innerHTML = `<span>Staged: ${file.name} (${fileSizeMB} MB)</span>`;
                    }
                };
                reader.readAsDataURL(file);
            });

            cancelBtn?.addEventListener('click', resetAvatarPreview);

            // Password Toggle Setup
            const setupToggle = (btnId, inputId) => {
                const btn = document.getElementById(btnId);
                const input = document.getElementById(inputId);
                btn?.addEventListener('click', () => {
                    if (!input) return;
                    input.type = input.type === 'password' ? 'text' : 'password';
                });
            };

            setupToggle('toggle-current-password', 'current_password');
            setupToggle('toggle-new-password', 'password');
            setupToggle('toggle-confirm-password', 'password_confirmation');

            // Password Real-time Checklist Validation
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const updatePwdBtn = document.getElementById('update-password-btn');

            const minLength = {{ $securitySettings?->min_password_length ?? 8 }};
            const reqUppercase = {{ ($securitySettings?->require_uppercase ?? false) ? 'true' : 'false' }};
            const reqNumeric = {{ ($securitySettings?->require_numeric ?? false) ? 'true' : 'false' }};
            const reqSpecial = {{ ($securitySettings?->require_special_char ?? false) ? 'true' : 'false' }};

            const updateRuleStatus = (elementId, isValid) => {
                const el = document.getElementById(elementId);
                if (!el) return;
                el.className = isValid 
                    ? 'flex items-center gap-2 text-emerald-600 font-medium transition' 
                    : 'flex items-center gap-2 text-slate-400 transition';
            };

            const validateChecklist = () => {
                if (!passwordInput || !confirmPasswordInput || !updatePwdBtn) return;
                const pwd = passwordInput.value;
                const confirmPwd = confirmPasswordInput.value;

                const validLength = pwd.length >= minLength;
                const validUpper = !reqUppercase || /[A-Z]/.test(pwd);
                const validNumeric = !reqNumeric || /[0-9]/.test(pwd);
                const validSpecial = !reqSpecial || /[^A-Za-z0-9]/.test(pwd);
                const validMatch = pwd.length > 0 && pwd === confirmPwd;

                updateRuleStatus('rule-length', validLength);
                if (reqUppercase) updateRuleStatus('rule-uppercase', validUpper);
                if (reqNumeric) updateRuleStatus('rule-numeric', validNumeric);
                if (reqSpecial) updateRuleStatus('rule-special', validSpecial);
                updateRuleStatus('rule-match', validMatch);

                updatePwdBtn.disabled = !(validLength && validUpper && validNumeric && validSpecial && validMatch);
            };

            passwordInput?.addEventListener('input', validateChecklist);
            confirmPasswordInput?.addEventListener('input', validateChecklist);
        });
    </script>
</body>
</html>