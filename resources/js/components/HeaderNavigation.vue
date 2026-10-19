<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      
      <!-- Brand Logo -->
      <a :href="dashboardUrl" class="flex items-center hover:opacity-90 transition">
        <img :src="logoUrl" alt="LODISv2" class="h-10 w-auto object-contain">
      </a>

      <!-- Right Action Navigation & User Dropdown -->
      <div class="flex items-center gap-3">
        
        <!-- RBAC Header Buttons -->
        <a 
          v-if="userRole === 'Superadmin' && routes.systemsIndex" 
          :href="routes.systemsIndex" 
          class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
          </svg>
          <span class="hidden sm:inline">System Management</span>
        </a>

        <template v-if="['Superadmin', 'Admin'].includes(userRole)">
          <a 
            v-if="routes.usersIndex" 
            :href="routes.usersIndex" 
            class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="hidden sm:inline">User Management</span>
          </a>

          <!-- Holiday Calendar Navigation Button -->
          <a 
            v-if="routes.holidaysIndex" 
            :href="routes.holidaysIndex" 
            class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 flex items-center gap-1.5"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="hidden sm:inline">Holiday Calendar</span>
          </a>
        </template>

        <!-- Notifications Dropdown -->
        <div ref="notifMenuWrapperRef" class="relative">
          <button 
            type="button" 
            @click="toggleNotifMenu"
            :aria-expanded="isNotifMenuOpen" 
            aria-haspopup="true"
            class="relative p-2 sm:p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition border border-slate-200 flex items-center justify-center"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span 
              v-if="localUnreadCount > 0" 
              class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white ring-2 ring-white"
            >
              {{ localUnreadCount > 9 ? '9+' : localUnreadCount }}
            </span>
          </button>

          <!-- Notifications Dropdown Card -->
          <div 
            v-show="isNotifMenuOpen" 
            class="absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50"
          >
            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <h3 class="text-xs font-extrabold text-slate-900">Notifications</h3>
                <span v-if="localUnreadCount > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-light text-brand">
                  <span>{{ localUnreadCount }}</span> New
                </span>
              </div>
              <form v-if="routes.markAllRead && localUnreadCount > 0" method="POST" :action="routes.markAllRead">
                <input type="hidden" name="_token" :value="csrfToken">
                <button type="submit" class="text-[11px] text-brand hover:text-brand-hover font-bold transition">
                  Mark all read
                </button>
              </form>
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
              <a 
                v-for="item in formattedHeaderNotifications" 
                :key="item.id"
                :href="item.read_url" 
                class="p-3.5 text-xs text-slate-700 hover:bg-slate-50 flex items-start gap-3 transition"
                :class="item.is_unread ? 'bg-amber-50/50' : ''"
              >
                <div class="w-8 h-8 rounded-lg bg-brand-light text-brand shrink-0 flex items-center justify-center font-bold text-xs mt-0.5">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-bold text-slate-900 truncate">{{ item.title }}</p>
                  <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ item.message }}</p>
                  <span class="text-[10px] text-slate-400 block mt-1 font-mono">
                    {{ item.created_at_human }}
                  </span>
                </div>
              </a>

              <div v-if="formattedHeaderNotifications.length === 0" class="p-6 text-center text-slate-500">
                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-xs font-bold text-slate-700">No new notifications</p>
                <p class="text-[11px] text-slate-400 mt-0.5">You are all caught up!</p>
              </div>
            </div>

            <div v-if="routes.notificationsIndex" class="p-2 border-t border-slate-100 bg-slate-50 text-center">
              <a :href="routes.notificationsIndex" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                View All Notifications
              </a>
            </div>
          </div>
        </div>

        <!-- User Menu Navigation Dropdown -->
        <div ref="userMenuWrapperRef" class="relative">
          <button 
            type="button" 
            @click="toggleUserMenu"
            :aria-expanded="isUserMenuOpen" 
            aria-haspopup="true"
            class="flex items-center gap-2.5 p-1.5 px-3 rounded-xl hover:bg-slate-100 transition border border-slate-200"
          >
            <img v-if="userPhoto" :src="userPhoto" :alt="fullName" class="w-7 h-7 rounded-lg object-cover shrink-0 border border-slate-200">
            <div v-else class="w-7 h-7 rounded-lg bg-brand-light text-brand font-bold text-xs flex items-center justify-center shrink-0 uppercase">
              {{ avatarInitial }}
            </div>

            <span class="text-xs font-bold text-slate-800 hidden sm:inline">{{ fullName }}</span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <!-- Dropdown Card -->
          <div 
            v-show="isUserMenuOpen" 
            class="absolute right-0 top-12 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50"
          >
            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center gap-3">
              <img v-if="userPhoto" :src="userPhoto" :alt="fullName" class="w-9 h-9 rounded-xl object-cover shrink-0 border border-slate-200">
              <div v-else class="w-9 h-9 rounded-xl bg-brand-light text-brand font-bold text-sm flex items-center justify-center shrink-0 uppercase">
                {{ avatarInitial }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-slate-900 truncate">{{ fullName }}</p>
                <span class="text-[10px] font-semibold text-slate-500 uppercase block mt-0.5">{{ userRole }}</span>
              </div>
            </div>

            <a :href="routes.profileEdit || '#'" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2.5 transition">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span>My Profile</span>
            </a>

            <a :href="profileSecurityUrl" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2.5 transition">
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

            <!-- Sign Out Button (Triggers Modal) -->
            <button 
              type="button" 
              @click="openLogoutModal"
              class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2.5 font-semibold transition cursor-pointer"
            >
              <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              <span>Sign Out</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- Custom Sign Out Confirmation Modal -->
  <Teleport to="body">
    <div 
      v-if="isLogoutModalOpen" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
      @click.self="closeLogoutModal"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-extrabold text-slate-900">Sign Out Session</h3>
            <p class="text-xs text-slate-500">Confirm ending your current workspace session.</p>
          </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
          Are you sure you want to sign out, <strong class="text-slate-900 font-bold">{{ fullName }}</strong>? 
          Any unsaved configuration or form changes will be lost.
        </p>

        <div class="flex items-center justify-end gap-2.5 pt-2">
          <button 
            type="button" 
            @click="closeLogoutModal" 
            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
          >
            Cancel
          </button>

          <form ref="logoutFormRef" method="POST" :action="routes.logout">
            <input type="hidden" name="_token" :value="csrfToken">
            <button 
              type="submit" 
              class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              <span>Confirm Sign Out</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  userRole: { type: String, default: 'User' },
  fullName: { type: String, default: 'User' },
  firstName: { type: String, default: '' },
  userPhoto: { type: String, default: null },
  authUserId: { type: [Number, String], default: null },
  unreadCount: { type: Number, default: 0 },
  notifications: { type: Array, default: () => [] },
  csrfToken: { type: String, required: true },
  logoUrl: { type: String, required: true },
  soundUrl: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      dashboard: '#',
      systemsIndex: null,
      usersIndex: null,
      holidaysIndex: null,
      markAllRead: null,
      notificationsIndex: null,
      profileEdit: '#',
      logout: '#',
    }),
  },
});

const isUserMenuOpen = ref(false);
const isNotifMenuOpen = ref(false);

const userMenuWrapperRef = ref(null);
const notifMenuWrapperRef = ref(null);

const localUnreadCount = ref(props.unreadCount);
const localNotifications = ref([...props.notifications]);

// Logout Modal State
const isLogoutModalOpen = ref(false);
const logoutFormRef = ref(null);

const openLogoutModal = () => {
  isUserMenuOpen.value = false;
  isLogoutModalOpen.value = true;
};

const closeLogoutModal = () => {
  isLogoutModalOpen.value = false;
};

const dashboardUrl = computed(() => props.routes?.dashboard || '/dashboard');

let notificationAudio = null;
let isAudioUnlocked = false;
let titleFlashTimer = null;

// Normalize notifications so raw DB notifications and realtime events both render cleanly
const formattedHeaderNotifications = computed(() => {
  return localNotifications.value.map((item) => {
    const data = item.data || {};
    const id = item.id || item.notification_id;

    const title = item.title || data.title || 'System Notification';
    const message = item.message || data.message || data.body || '';

    // Route through readAndRedirect controller endpoint if a valid ID exists
    let readUrl = item.read_url;
    if (!readUrl || readUrl === '#') {
      if (id && typeof id === 'string' && id.length > 20) {
        readUrl = `/notifications/${id}/read`;
      } else {
        readUrl = item.url || data.url || '/users';
      }
    }

    const isUnread = item.is_new || item.is_unread || item.read_at === null;

    return {
      ...item,
      id,
      title,
      message,
      read_url: readUrl,
      is_unread: isUnread,
      created_at_human: item.created_at_human || 'Just now',
    };
  });
});

const avatarInitial = computed(() => {
  const target = props.firstName || props.fullName || 'U';
  return target.charAt(0).toUpperCase();
});

const profileSecurityUrl = computed(() => {
  if (!props.routes.profileEdit) return '#';
  return `${props.routes.profileEdit}#password`;
});

const toggleUserMenu = () => {
  isNotifMenuOpen.value = false;
  isUserMenuOpen.value = !isUserMenuOpen.value;
};

const toggleNotifMenu = () => {
  isUserMenuOpen.value = false;
  isNotifMenuOpen.value = !isNotifMenuOpen.value;
};

const handleDocumentClick = (e) => {
  if (userMenuWrapperRef.value && !userMenuWrapperRef.value.contains(e.target)) {
    isUserMenuOpen.value = false;
  }
  if (notifMenuWrapperRef.value && !notifMenuWrapperRef.value.contains(e.target)) {
    isNotifMenuOpen.value = false;
  }
  stopTitleFlashing();
};

// Audio & Notification Helpers
const unlockAudio = () => {
  if (!isAudioUnlocked && notificationAudio) {
    notificationAudio.play().then(() => {
      notificationAudio.pause();
      notificationAudio.currentTime = 0;
      isAudioUnlocked = true;
    }).catch(() => {});
    document.removeEventListener('click', unlockAudio);
    document.removeEventListener('keydown', unlockAudio);
  }
};

const getBaseTitle = () => document.title.replace(/^(\(\d+\+?\)\s*|🔔\s*\(?\d+\+?\)?\s*)/, '').trim();

const startTitleFlashing = (flashText = 'New Notification!') => {
  stopTitleFlashing();
  let toggle = false;
  const baseTitle = getBaseTitle();
  const badgeStr = localUnreadCount.value > 0 ? `(${localUnreadCount.value > 9 ? '9+' : localUnreadCount.value})` : '';

  titleFlashTimer = setInterval(() => {
    document.title = toggle ? `${badgeStr} ${baseTitle}`.trim() : `🔔 ${flashText}`;
    toggle = !toggle;
  }, 1000);
};

const stopTitleFlashing = () => {
  if (titleFlashTimer) {
    clearInterval(titleFlashTimer);
    titleFlashTimer = null;
  }
  const baseTitle = getBaseTitle();
  if (localUnreadCount.value > 0) {
    const badgeStr = localUnreadCount.value > 9 ? '(9+)' : `(${localUnreadCount.value})`;
    document.title = `${badgeStr} ${baseTitle}`;
  } else {
    document.title = baseTitle;
  }
};

const playNotificationChime = (title = 'Notification', body = 'New notification received.') => {
  if (notificationAudio) {
    notificationAudio.currentTime = 0;
    notificationAudio.play().catch(() => {});
  }

  if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
    const desktopNotif = new Notification(title, {
      body,
      icon: props.logoUrl,
      tag: 'system-notification',
    });
    desktopNotif.onclick = () => {
      window.focus();
      desktopNotif.close();
    };
  }
};

// Centralized Notification Ingestion Handler
const ingestNotification = (payload) => {
  localUnreadCount.value += 1;

  const title = payload.title || 'New Notification';
  const msg = payload.message || payload.body || '';
  const notifId = payload.notification_id || payload.id;
  const readUrl = notifId ? `/notifications/${notifId}/read` : (payload.url || '/users');

  localNotifications.value.unshift({
    id: notifId || Date.now(),
    title,
    message: msg,
    url: payload.url || '/users',
    read_url: readUrl,
    created_at_human: 'Just now',
    is_new: true,
  });

  playNotificationChime(title, msg);
  startTitleFlashing(title);
};

// Centralized Echo Setup
const registerEchoListener = () => {
  if (!props.authUserId) return;

  if (typeof window.Echo !== 'undefined') {
    window.Echo.private(`user-security.${props.authUserId}`)
      // Workstation Sign-In Requests
      .listen('.device.approval.requested', (e) => {
        if (e.requestData) {
          ingestNotification({
            id: e.requestData.notification_id || e.requestData.id,
            title: 'New Workstation Sign-In Request',
            message: `Authorization requested for ${e.requestData.device_name || 'Workstation'} (${e.requestData.ip_address || 'N/A'})`,
            url: `/device/review/${e.requestData.id}`,
          });
        }
      })
      // Real-Time New User Registration Alerts
      .listen('.user.registered', (e) => {
        ingestNotification({
          id: e.notification_id || e.id,
          notification_id: e.notification_id,
          title: e.title || 'New User Registration',
          message: e.message || '',
          url: e.url || '/users',
        });
      });
  } else {
    setTimeout(registerEchoListener, 300);
  }
};

onMounted(() => {
  notificationAudio = new Audio(props.soundUrl);
  notificationAudio.volume = 1.0;

  document.addEventListener('click', unlockAudio);
  document.addEventListener('keydown', unlockAudio);
  document.addEventListener('click', handleDocumentClick);
  window.addEventListener('focus', stopTitleFlashing);

  registerEchoListener();
});

onUnmounted(() => {
  document.removeEventListener('click', unlockAudio);
  document.removeEventListener('keydown', unlockAudio);
  document.removeEventListener('click', handleDocumentClick);
  window.removeEventListener('focus', stopTitleFlashing);

  if (props.authUserId && typeof window.Echo !== 'undefined') {
    window.Echo.leave(`user-security.${props.authUserId}`);
  }
});
</script>