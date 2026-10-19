<template>
  <div>
    <!-- Status Toast Alert -->
    <div v-if="statusMessage" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ statusMessage }}</span>
      </div>
    </div>

    <!-- Page Title & Global Actions Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Notification Center</h1>
        <p class="text-xs text-slate-500 mt-1">Manage system notifications, real-time security alerts, and event updates.</p>
      </div>

      <form v-if="unreadCount > 0 && markAllReadUrl" method="POST" :action="markAllReadUrl">
        <input type="hidden" name="_token" :value="csrfToken">
        <button type="submit" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition flex items-center gap-2 shadow-sm">
          <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <span>Mark All as Read</span>
        </button>
      </form>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-slate-200 flex items-center gap-6 text-xs font-semibold">
      <a 
        :href="filterUrls.all" 
        class="pb-3 border-b-2 transition flex items-center gap-2"
        :class="currentFilter === 'all' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
      >
        <span>All Notifications</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-700 font-bold">
          {{ totalCount }}
        </span>
      </a>

      <a 
        :href="filterUrls.unread" 
        class="pb-3 border-b-2 transition flex items-center gap-2"
        :class="currentFilter === 'unread' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
      >
        <span>Unread Only</span>
        <span v-if="unreadCount > 0" class="px-2 py-0.5 rounded-full text-[10px] bg-brand text-white font-bold">
          {{ unreadCount }}
        </span>
      </a>
    </div>

    <!-- Notification Cards List -->
    <div class="space-y-3">
      <div 
        v-for="notification in notifications" 
        :key="notification.id"
        @click="navigateToUrl(notification.read_url)"
        class="bg-white rounded-2xl border transition duration-200 p-4 sm:p-5 flex items-start gap-4 shadow-sm hover:shadow-md relative overflow-hidden cursor-pointer"
        :class="notification.is_unread ? 'border-brand/40 bg-slate-50/50' : 'border-slate-200'"
      >
        <span v-if="notification.is_unread" class="absolute left-0 top-0 bottom-0 w-1 bg-brand"></span>

        <!-- Category Icon -->
        <div 
          class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center font-bold text-sm mt-0.5 pointer-events-none"
          :class="notification.data.type === 'security_alert' ? 'bg-amber-100 text-amber-700' : 'bg-brand-light text-brand'"
        >
          <svg v-if="notification.data.type === 'security_alert'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>

        <!-- Details Body -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs sm:text-sm font-extrabold text-slate-900 hover:text-brand transition truncate">
              {{ notification.data.title || 'System Update' }}
            </span>
            <span class="text-[11px] text-slate-400 shrink-0 font-mono">
              {{ notification.created_at_human }}
            </span>
          </div>

          <p class="text-xs text-slate-600 mt-1 leading-relaxed">
            {{ notification.data.message || 'You have a new system alert.' }}
          </p>

          <div v-if="notification.data.ip_address" class="mt-2 text-[10px] text-slate-500 font-mono flex items-center gap-3">
            <span>IP: {{ notification.data.ip_address }}</span>
            <span>Platform: {{ notification.data.platform || 'N/A' }}</span>
          </div>
        </div>

        <!-- Item Actions -->
        <div class="flex items-center gap-1 shrink-0 self-center" @click.stop>
          <form v-if="notification.is_unread && notification.mark_read_url" method="POST" :action="notification.mark_read_url">
            <input type="hidden" name="_token" :value="csrfToken">
            <button type="submit" title="Mark as read" class="p-2 rounded-lg text-slate-400 hover:text-brand hover:bg-slate-100 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </button>
          </form>

          <form v-if="notification.destroy_url" method="POST" :action="notification.destroy_url">
            <input type="hidden" name="_token" :value="csrfToken">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" title="Delete notification" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </form>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="notifications.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <h3 class="text-sm font-bold text-slate-800">No notifications found</h3>
        <p class="text-xs text-slate-500 mt-1">You are all caught up on system updates and security alerts.</p>
      </div>
    </div>

    <!-- Pagination Slot -->
    <div v-if="paginationHtml" class="mt-6" v-html="paginationHtml"></div>
  </div>
</template>

<script setup>
defineProps({
  notifications: { type: Array, default: () => [] },
  totalCount: { type: Number, default: 0 },
  unreadCount: { type: Number, default: 0 },
  currentFilter: { type: String, default: 'all' },
  filterUrls: {
    type: Object,
    default: () => ({ all: '#', unread: '#' }),
  },
  markAllReadUrl: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  statusMessage: { type: String, default: '' },
  paginationHtml: { type: String, default: '' },
});

const navigateToUrl = (url) => {
  if (url && url !== '#') {
    window.location.href = url;
  }
};
</script>