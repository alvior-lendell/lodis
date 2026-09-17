<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Holiday Calendar - LODISv2</title>

    <link rel="icon" type="image/png" href="{{ asset('images/LODISv2.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <meta name="theme-color" content="#00687A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50/60 text-slate-900 flex flex-col">

    @include('partials.header')

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800 flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs font-medium text-rose-700 shadow-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Action Header -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Philippine Holiday Schedule</h1>
                <p class="text-xs text-slate-500 mt-1">Manage and sync official PH holidays into the dedicated `holidays` database table.</p>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap sm:flex-nowrap">
                <form method="GET" action="{{ route('holidays.index') }}" class="flex items-center">
                    <select name="year" onchange="this.form.submit()" class="text-xs font-bold rounded-2xl border-slate-200 bg-slate-50 text-slate-700 py-2.5 px-3.5 focus:ring-4 focus:ring-brand/15 focus:border-brand transition">
                        @for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                        @endfor
                    </select>
                </form>

                <button type="button" id="open-modal-btn" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Holiday</span>
                </button>

                <form method="POST" action="{{ route('holidays.sync') }}">
                    @csrf
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <button type="submit" onclick="return confirm('Pull and sync official PH holidays for {{ $selectedYear }} via MCP?')" 
                        class="px-4 py-2.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>One-Click Pull PH Holidays</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
                            <th class="py-4 px-6">Date</th>
                            <th class="py-4 px-6">Holiday Name</th>
                            <th class="py-4 px-6">Type & Tags</th>
                            <th class="py-4 px-6">Proclamation / Notes</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($holidays as $holiday)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-6 font-bold text-slate-800 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}
                                    <span class="block text-[10px] font-normal text-slate-400">
                                        {{ $holiday->day_of_week ?: \Carbon\Carbon::parse($holiday->date)->format('l') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block">{{ $holiday->name }}</span>
                                    @if ($holiday->double_holiday && !empty($holiday->double_holiday_names))
                                        <span class="text-[10px] text-purple-600 font-semibold block mt-0.5">
                                            Double Holiday: {{ implode(' & ', $holiday->double_holiday_names) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap space-y-1">
                                    @php
                                        $typeStyles = [
                                            'regular'             => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'special_non_working' => 'bg-purple-50 text-purple-800 border-purple-200',
                                            'special_working'     => 'bg-sky-50 text-sky-800 border-sky-200',
                                            'islamic'             => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        ];
                                        $typeLabels = [
                                            'regular'             => 'Regular Holiday',
                                            'special_non_working' => 'Special Non-Working',
                                            'special_working'     => 'Special Working',
                                            'islamic'             => 'Islamic Holiday',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-extrabold border {{ $typeStyles[$holiday->type] ?? 'bg-slate-50 text-slate-700' }}">
                                        {{ $typeLabels[$holiday->type] ?? ucfirst($holiday->type) }}
                                    </span>

                                    @if ($holiday->is_part_of_long_weekend)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                            Long Weekend
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($holiday->proclamation_ref)
                                        <span class="text-[11px] font-semibold text-slate-700 block">{{ $holiday->proclamation_ref }}</span>
                                    @endif
                                    @if ($holiday->notes)
                                        <span class="text-[10px] text-slate-400 truncate block max-w-xs">{{ $holiday->notes }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete holiday \'{{ $holiday->name }}\'?')" class="text-rose-600 hover:text-rose-800 text-xs font-bold transition">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500">
                                    No holidays found for {{ $selectedYear }}. Click <span class="font-bold text-brand">One-Click Pull PH Holidays</span> to sync automatically.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Create Holiday Modal -->
    <div id="add-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-extrabold text-slate-900">Add Holiday Entry</h3>
                <button type="button" id="close-modal-btn" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Holiday Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Independence Day" class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Date *</label>
                        <input type="date" name="date" required class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Type *</label>
                        <select name="type" required class="form-select block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                            <option value="regular">Regular Holiday</option>
                            <option value="special_non_working">Special Non-Working</option>
                            <option value="special_working">Special Working</option>
                            <option value="islamic">Islamic Holiday</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Proclamation Reference</label>
                    <input type="text" name="proclamation_ref" placeholder="e.g. Proclamation No. 1006" class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional context..." class="form-textarea block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition resize-none focus:border-brand focus:ring-4 focus:ring-brand/15"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-5 py-3 rounded-2xl bg-brand text-white text-xs font-bold transition hover:bg-brand-hover">Save Holiday</button>
                    <button type="button" id="cancel-modal-btn" class="px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs font-bold transition hover:bg-slate-100">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('add-modal');
            const toggle = (show) => modal?.classList.toggle('hidden', !show);
            document.getElementById('open-modal-btn')?.addEventListener('click', () => toggle(true));
            document.getElementById('close-modal-btn')?.addEventListener('click', () => toggle(false));
            document.getElementById('cancel-modal-btn')?.addEventListener('click', () => toggle(false));
        });
    </script>
</body>
</html>