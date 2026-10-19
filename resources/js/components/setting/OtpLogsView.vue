<template>
  <div class="space-y-6">
    <!-- Status Toast Alert -->
    <div v-if="localStatusMessage" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ localStatusMessage }}</span>
      </div>
      <button type="button" @click="localStatusMessage = ''" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1">&times;</button>
    </div>

    <!-- Action Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">OTP Verification Audit Logs</h2>
        <p class="text-xs text-slate-500 mt-1">Monitor real-time two-factor authentication attempts, expiration windows, and resend statistics.</p>
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto">
        <a 
          :href="routes.usersIndex" 
          class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          <span>Back to User Directory</span>
        </a>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
      <form method="GET" :action="routes.index" ref="filterFormRef" class="flex flex-col sm:flex-row items-center gap-3">
        <div class="w-full sm:flex-1">
          <input 
            type="text" 
            name="search" 
            v-model="searchQuery" 
            placeholder="Search by email address or employee ID..." 
            class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition"
          >
        </div>

        <div class="w-full sm:w-48">
          <select 
            name="status" 
            v-model="selectedStatus" 
            @change="submitFilter" 
            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition bg-white"
          >
            <option value="">All Statuses</option>
            <option value="active">Active (Pending)</option>
            <option value="used">Used</option>
            <option value="expired">Expired</option>
          </select>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
            Filter
          </button>
          <a 
            v-if="hasActiveFilters" 
            :href="routes.index" 
            class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition"
          >
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- OTP Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
            <tr>
              <th class="py-4 px-6">User / Email</th>
              <th class="py-4 px-6">Employee ID</th>
              <th class="py-4 px-6">OTP Code</th>
              <th class="py-4 px-6">Resend Attempts</th>
              <th class="py-4 px-6">Verification Status</th>
              <th class="py-4 px-6">Expires At</th>
              <th class="py-4 px-6">Created At</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/50 transition">
              <!-- Email -->
              <td class="py-4 px-6 font-bold text-slate-900 font-mono">
                {{ log.email }}
              </td>

              <!-- Employee ID -->
              <td class="py-4 px-6 font-mono text-slate-600">
                {{ log.employee_id }}
              </td>

              <!-- OTP Code -->
              <td class="py-4 px-6 font-mono">
                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-900 font-extrabold border border-amber-200 tracking-widest text-xs">
                  {{ log.otp_code || '------' }}
                </span>
              </td>

              <!-- Resend Attempts Badge -->
              <td class="py-4 px-6">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold border border-slate-200">
                  {{ log.resend_attempts }} / 3
                </span>
              </td>

              <!-- Verification Status -->
              <td class="py-4 px-6 whitespace-nowrap">
                <span 
                  v-if="log.is_used" 
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  <span>Used</span>
                </span>

                <span 
                  v-else-if="log.is_expired" 
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1.5"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                  <span>Expired</span>
                </span>

                <span 
                  v-else 
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 inline-flex items-center gap-1.5"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                  <span>Active / Pending</span>
                </span>
              </td>

              <!-- Expiration Timestamp -->
              <td class="py-4 px-6 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                {{ log.expires_at }}
              </td>

              <!-- Sent Timestamp -->
              <td class="py-4 px-6 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                {{ log.created_at }}
              </td>

              <!-- Actions -->
              <td class="py-4 px-6 text-right whitespace-nowrap">
                <button 
                  type="button" 
                  @click="openDeleteModal(log)" 
                  class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition border border-rose-200" 
                  title="Delete Log Entry"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td colspan="8" class="py-12 text-center text-slate-500 font-semibold">
                No OTP verification records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="paginationHtml" class="p-4 border-t border-slate-100 bg-slate-50" v-html="paginationHtml"></div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isDeleteModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeDeleteModal"
      >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Delete OTP Record</h3>
              <p class="text-xs text-slate-500">This action is permanent and cannot be undone.</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to delete the OTP verification log for 
            <strong class="text-slate-900 font-bold font-mono">{{ selectedLogForDelete?.email }}</strong>?
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeDeleteModal" 
              class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <form method="POST" :action="selectedLogForDelete?.destroy_url">
              <input type="hidden" name="_token" :value="csrfToken">
              <input type="hidden" name="_method" value="DELETE">
              <button 
                type="submit" 
                class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Confirm Delete</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  logs: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({ search: '', status: '' }),
  },
  statusMessage: { type: String, default: '' },
  paginationHtml: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      index: '#',
      usersIndex: '#',
    }),
  },
});

const localStatusMessage = ref(props.statusMessage);
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const filterFormRef = ref(null);

// Modal state
const isDeleteModalOpen = ref(false);
const selectedLogForDelete = ref(null);

const openDeleteModal = (log) => {
  selectedLogForDelete.value = log;
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  selectedLogForDelete.value = null;
};

const hasActiveFilters = computed(() => {
  return !!(props.filters.search || props.filters.status);
});

const submitFilter = () => {
  if (filterFormRef.value) {
    filterFormRef.value.submit();
  }
};
</script>