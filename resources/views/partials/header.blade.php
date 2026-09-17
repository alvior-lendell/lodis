@php
    $authUser = auth()->user();
    $userRole = $authUser?->role ?? 'Employee';
    
    // Resolve employee first & last name directly from relation or user attributes
    $employee = $authUser?->employee;
    $firstName = $employee?->first_name ?? $authUser?->first_name ?? '';
    $lastName = $employee?->last_name ?? $authUser?->last_name ?? '';
    $fullName = trim("{$firstName} {$lastName}") ?: ($authUser?->name ?? 'User');

    // Notifications resolution
    $notifications = $authUser?->unreadNotifications()->take(5)->get() ?? collect();
    $unreadCount = $authUser?->unreadNotifications()->count() ?? 0;
@endphp

<!-- Top Navigation Bar -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Clean Brand Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center hover:opacity-90 transition">
            <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-10 w-auto object-contain">
        </a>

        <!-- Right Action Navigation & User Dropdown -->
        <div class="flex items-center gap-3">
            
            <!-- RBAC Header Buttons -->
            @if ($userRole === 'Superadmin')
                <a href="{{ Route::has('systems.index') ? route('systems.index') : '#' }}" 
                    class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span class="hidden sm:inline">System Management</span>
                </a>
            @endif

            @if (in_array($userRole, ['Superadmin', 'Admin']))
                <a href="{{ Route::has('users.index') ? route('users.index') : '#' }}" 
                    class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="hidden sm:inline">User Management</span>
                </a>

                <!-- Holiday Calendar Navigation Button -->
                <a href="{{ Route::has('holidays.index') ? route('holidays.index') : '#' }}" 
                    class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Holiday Calendar</span>
                </a>
            @endif

            <!-- Notifications Dropdown -->
            <div class="relative" id="notification-menu-wrapper">
                <button type="button" id="notification-menu-button" 
                    aria-expanded="false" aria-haspopup="true"
                    class="relative p-2 sm:p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition border border-slate-200 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if ($unreadCount > 0)
                        <span id="notif-badge-counter" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white ring-2 ring-white">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <!-- Notifications Dropdown Card -->
                <div id="notification-menu-dropdown" class="hidden absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50">
                    <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-extrabold text-slate-900">Notifications</h3>
                            @if ($unreadCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-light text-brand">
                                    {{ $unreadCount }} New
                                </span>
                            @endif
                        </div>
                        @if ($unreadCount > 0 && Route::has('notifications.markAllRead'))
                            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                @csrf
                                <button type="submit" class="text-[11px] text-brand hover:text-brand-hover font-bold transition">
                                    Mark all read
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                        @forelse ($notifications as $notification)
                            <a href="{{ $notification->data['url'] ?? '#' }}" class="p-3.5 text-xs text-slate-700 hover:bg-slate-50 flex items-start gap-3 transition">
                                <div class="w-8 h-8 rounded-lg bg-brand-light text-brand shrink-0 flex items-center justify-center font-bold text-xs mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $notification->data['title'] ?? 'System Update' }}</p>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $notification->data['message'] ?? 'You have a new system alert.' }}</p>
                                    <span class="text-[10px] text-slate-400 block mt-1 font-mono">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="p-6 text-center text-slate-500">
                                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-xs font-bold text-slate-700">No new notifications</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">You are all caught up!</p>
                            </div>
                        @endforelse
                    </div>

                    @if (Route::has('notifications.index'))
                        <div class="p-2 border-t border-slate-100 bg-slate-50 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                                View All Notifications
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User Menu Navigation Dropdown -->
            <div class="relative" id="user-menu-wrapper">
                <button type="button" id="user-menu-button" 
                    aria-expanded="false" aria-haspopup="true"
                    class="flex items-center gap-2.5 p-1.5 px-3 rounded-xl hover:bg-slate-100 transition border border-slate-200">
                    <div class="w-7 h-7 rounded-lg bg-brand-light text-brand font-bold text-xs flex items-center justify-center shrink-0 uppercase">
                        {{ strtoupper(substr($firstName ?: $fullName, 0, 1)) }}
                    </div>
                    <span class="text-xs font-bold text-slate-800 hidden sm:inline">{{ $fullName }}</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Clean Dropdown Card -->
                <div id="user-menu-dropdown" class="hidden absolute right-0 top-12 w-60 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50">
                    <div class="px-4 py-2 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $fullName }}</p>
                        <span class="text-[10px] font-semibold text-slate-500 uppercase">{{ $userRole }}</span>
                    </div>

                    <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" 
                        class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2.5 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>My Profile</span>
                    </a>

                    <a href="{{ Route::has('profile.edit') ? route('profile.edit') . '#password' : '#' }}" 
                        class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2.5 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Password & Security</span>
                    </a>

                    <div class="border-t border-slate-100 my-1"></div>

                    <a href="mailto:it-support@lendell.ph" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2.5 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>IT Support & Helpdesk</span>
                    </a>

                    <div class="border-t border-slate-100 my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                            class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2.5 font-semibold transition">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Single Authenticated Reverb Device Approval Modal -->
@auth
<div id="device-auth-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center space-y-4">
        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <h3 class="text-base font-extrabold text-slate-900">New Device Sign-In Attempt</h3>
            <p class="text-xs text-slate-500 mt-1">A new workstation is attempting to sign into your account.</p>
        </div>

        <div class="bg-slate-50 rounded-2xl p-4 space-y-2 text-xs text-left border border-slate-200/80">
            <div class="flex justify-between"><span class="text-slate-500">Device:</span><span id="auth-device-name" class="font-bold text-slate-800">--</span></div>
            <div class="flex justify-between"><span class="text-slate-500">IP Address:</span><span id="auth-ip-address" class="font-mono text-slate-800">--</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Time:</span><span id="auth-time" class="text-slate-800">--</span></div>
        </div>

        <div class="flex gap-3">
            <form id="device-reject-form" method="POST" action="" class="w-1/2">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                    Reject Device
                </button>
            </form>
            <form id="device-approve-form" method="POST" action="" class="w-1/2">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl bg-brand text-white text-xs font-bold hover:bg-brand-hover shadow-md shadow-brand/20 transition">
                    Authorize Device
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    if (typeof window.headerDropdownInitialized === 'undefined') {
        window.headerDropdownInitialized = true;

        // 1. Audio Notification Setup
        const notificationAudio = new Audio("{{ asset('sounds/notification.mp3') }}");
        notificationAudio.volume = 1.0;

        let isAudioUnlocked = false;
        const unlockAudio = () => {
            if (!isAudioUnlocked) {
                notificationAudio.play().then(() => {
                    notificationAudio.pause();
                    notificationAudio.currentTime = 0;
                    isAudioUnlocked = true;
                }).catch(() => {});
                document.removeEventListener('click', unlockAudio);
                document.removeEventListener('keydown', unlockAudio);
            }
        };
        document.addEventListener('click', unlockAudio);
        document.addEventListener('keydown', unlockAudio);

        // 2. Request OS Desktop Notification Permission
        const requestDesktopNotificationPermission = () => {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        };
        document.addEventListener('click', requestDesktopNotificationPermission, { once: true });

        // 3. Facebook-Style Title Flashing Engine
        const baseDocumentTitle = document.title.replace(/^(\(\d+\+?\)\s*|🔔\s*\(?\d+\+?\)?\s*)/, '');
        let currentUnreadCount = {{ (int) $unreadCount }};
        let titleFlashTimer = null;

        window.startTitleFlashing = function (flashText = 'New Notification!') {
            window.stopTitleFlashing();
            
            let toggle = false;
            const badgeStr = currentUnreadCount > 0 ? `(${currentUnreadCount > 9 ? '9+' : currentUnreadCount})` : '';

            titleFlashTimer = setInterval(() => {
                if (toggle) {
                    document.title = `${badgeStr} ${baseDocumentTitle}`.trim();
                } else {
                    document.title = `🔔 ${flashText}`;
                }
                toggle = !toggle;
            }, 1000);
        };

        window.stopTitleFlashing = function () {
            if (titleFlashTimer) {
                clearInterval(titleFlashTimer);
                titleFlashTimer = null;
            }
            if (currentUnreadCount > 0) {
                const badgeStr = currentUnreadCount > 9 ? '(9+)' : `(${currentUnreadCount})`;
                document.title = `${badgeStr} ${baseDocumentTitle}`;
            } else {
                document.title = baseDocumentTitle;
            }
        };

        // Stop title flashing when the user clicks or focuses on the window
        window.addEventListener('focus', () => {
            window.stopTitleFlashing();
        });
        document.addEventListener('click', () => {
            window.stopTitleFlashing();
        });

        // 4. Audio & Desktop Alert Dispatcher
        window.playNotificationChime = function (title = 'Security Alert', body = 'New notification received.') {
            notificationAudio.currentTime = 0;
            notificationAudio.play().catch(() => {});

            if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
                const desktopNotif = new Notification(title, {
                    body: body,
                    icon: "{{ asset('images/LODISv2.png') }}",
                    tag: 'device-auth-alert',
                });

                desktopNotif.onclick = () => {
                    window.focus();
                    desktopNotif.close();
                };
            }
        };

        // 5. Tab Badge & Title Updater
        window.updateTabNotificationBadge = function (count) {
            currentUnreadCount = count;
            window.stopTitleFlashing();
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.updateTabNotificationBadge(currentUnreadCount);

            // Dropdown Toggle Handlers
            const userBtn = document.getElementById('user-menu-button');
            const userDropdown = document.getElementById('user-menu-dropdown');
            const notifBtn = document.getElementById('notification-menu-button');
            const notifDropdown = document.getElementById('notification-menu-dropdown');

            userBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown?.classList.add('hidden');
                notifBtn?.setAttribute('aria-expanded', 'false');
                const isHidden = userDropdown?.classList.toggle('hidden');
                userBtn.setAttribute('aria-expanded', (!isHidden).toString());
            });

            notifBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown?.classList.add('hidden');
                userBtn?.setAttribute('aria-expanded', 'false');
                const isHidden = notifDropdown?.classList.toggle('hidden');
                notifBtn.setAttribute('aria-expanded', (!isHidden).toString());
            });

            document.addEventListener('click', (e) => {
                if (!document.getElementById('user-menu-wrapper')?.contains(e.target)) {
                    userDropdown?.classList.add('hidden');
                    userBtn?.setAttribute('aria-expanded', 'false');
                }
                if (!document.getElementById('notification-menu-wrapper')?.contains(e.target)) {
                    notifDropdown?.classList.add('hidden');
                    notifBtn?.setAttribute('aria-expanded', 'false');
                }
            });

            // Reverb Private Channel Real-Time Listener
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('user-security.{{ auth()->id() }}')
                    .listen('.device.approval.requested', (e) => {
                        const data = e.requestData;
                        if (data) {
                            document.getElementById('auth-device-name').textContent = data.device_name || 'Unknown Device';
                            document.getElementById('auth-ip-address').textContent = data.ip_address || 'N/A';
                            document.getElementById('auth-time').textContent = data.created_at || 'Just now';
                            document.getElementById('device-approve-form').action = `/auth/device-approve/${data.id}`;
                            document.getElementById('device-reject-form').action = `/auth/device-reject/${data.id}`;
                            document.getElementById('device-auth-modal').classList.remove('hidden');

                            // 1. Increment count & Update badge
                            currentUnreadCount += 1;

                            // 2. Play Sound & OS Desktop Toast Banner
                            window.playNotificationChime(
                                'New Device Sign-In Attempt',
                                `Authorization requested for ${data.device_name || 'a new workstation'} (${data.ip_address || 'N/A'}).`
                            );

                            // 3. Start Facebook-style Title Flashing
                            window.startTitleFlashing('New Security Alert!');
                        }
                    });
            }
        });
    }
</script>
@endauth