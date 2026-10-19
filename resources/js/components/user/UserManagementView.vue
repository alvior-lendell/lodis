<template>
  <div class="space-y-6">
    <!-- Status Toast Alert -->
    <div v-if="localStatusMessage" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ localStatusMessage }}</span>
      </div>
      <button type="button" @click="localStatusMessage = ''" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1">&times;</button>
    </div>

    <!-- Header Title & Action Buttons -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">System User Directory</h2>
      <p class="text-xs text-slate-500 mt-1">Manage employee access privileges, operational roles, and security credentials.</p>
    </div>

    <div class="flex items-center gap-3 w-fit flex-wrap">
      <!-- OTP Verifications Button (Admin) -->
      <a 
        v-if="routes.otpLogs" 
        :href="routes.otpLogs" 
        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-2 w-fit"
        title="View OTP Verification Logs"
      >
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>OTP Verifications</span>
      </a>

      <!-- User System Access Button -->
      <a 
        v-if="routes.systemAccess" 
        :href="routes.systemAccess" 
        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-2 w-fit"
      >
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/>
        </svg>
        <span>User System Access</span>
      </a>

      <!-- Register New User Button -->
      <a 
        v-if="routes.create" 
        :href="routes.create" 
        class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-2 w-fit"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Register New User</span>
      </a>
    </div>
  </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
      <form method="GET" :action="routes.index" ref="filterFormRef" class="flex flex-col sm:flex-row items-center gap-3">
        <div class="w-full sm:flex-1">
          <input 
            type="text" 
            name="search" 
            v-model="searchQuery" 
            placeholder="Search by name, email, or employee ID..." 
            class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition"
          >
        </div>

        <div class="w-full sm:w-48">
          <select 
            name="role" 
            v-model="selectedRole" 
            @change="submitFilter" 
            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition bg-white"
          >
            <option value="">All Roles</option>
            <option v-if="isSuperadmin" value="Superadmin">Superadmin</option>
            <option value="Admin">Admin</option>
            <option value="Employee">Employee</option>
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

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
              <th class="py-3.5 px-6">Employee ID</th>
              <th class="py-3.5 px-6">Name & Email</th>
              <th class="py-3.5 px-6">Role</th>
              <th class="py-3.5 px-6">Status & Activation</th>
              <th class="py-3.5 px-6">Last Password Change</th>
              <th class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr 
              v-for="user in sortedUsers" 
              :key="user.id" 
              class="transition"
              :class="!user.is_active ? 'bg-amber-50/30 hover:bg-amber-50/60' : 'hover:bg-slate-50/50'"
            >
              <!-- Employee ID -->
              <td class="py-4 px-6 font-mono font-bold text-slate-900">
                {{ user.employee_id || '—' }}
              </td>

              <!-- Name & Email with Tag for New/Pending Users -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900 text-sm">{{ user.name }}</span>
                  <span 
                    v-if="!user.is_active || user.is_new" 
                    class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs flex items-center gap-1 shrink-0"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>New Request</span>
                  </span>
                </div>
                <div class="text-slate-500 text-[11px] font-mono mt-0.5">{{ user.email }}</div>
              </td>

              <!-- Role -->
              <td class="py-4 px-6">
                <span v-if="user.role === 'Superadmin'" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Superadmin</span>
                <span v-else-if="user.role === 'Admin'" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Admin</span>
                <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Employee</span>
              </td>

              <!-- Status Column with Quick Activate Button -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-2.5">
                  <span 
                    v-if="user.is_active" 
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Active</span>
                  </span>

                  <!-- Quick Activate Action Button for Unactivated Users -->
                  <template v-else>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                      Pending
                    </span>

                    <form 
                      v-if="user.activate_url || user.toggle_status_url" 
                      method="POST" 
                      :action="user.activate_url || user.toggle_status_url"
                    >
                      <input type="hidden" name="_token" :value="csrfToken">
                      <input type="hidden" name="_method" value="PATCH">
                      <button 
                        type="submit" 
                        class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition shadow-2xs flex items-center gap-1 shrink-0"
                        title="Click to activate this account"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Activate</span>
                      </button>
                    </form>
                  </template>
                </div>
              </td>

              <!-- Last Password Change -->
              <td class="py-4 px-6 text-slate-500 font-mono text-[11px]">
                {{ user.password_changed_at_formatted || 'Never' }}
              </td>

              <!-- Actions -->
              <td class="py-4 px-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a 
                    :href="user.edit_url" 
                    class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition border border-slate-200" 
                    title="Edit User"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </a>

                  <!-- Triggers Custom Delete Confirmation Modal -->
                  <button 
                    v-if="isSuperadmin && currentUserId !== user.id" 
                    type="button" 
                    @click="openDeleteModal(user)"
                    class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition border border-rose-200" 
                    title="Delete User"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="sortedUsers.length === 0">
              <td colspan="6" class="py-12 text-center text-slate-500 font-semibold">
                No user accounts matched your search parameters.
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
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Delete User Account</h3>
              <p class="text-xs text-slate-500">This action is permanent and cannot be undone.</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to permanently delete the system account for 
            <strong class="text-slate-900 font-bold">{{ selectedUserForDelete?.name }}</strong> 
            (<span class="font-mono text-slate-700">{{ selectedUserForDelete?.email }}</span>)?
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeDeleteModal" 
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <form method="POST" :action="selectedUserForDelete?.destroy_url">
              <input type="hidden" name="_token" :value="csrfToken">
              <input type="hidden" name="_method" value="DELETE">
              <button 
                type="submit" 
                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
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
  users: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({ search: '', role: '' }),
  },
  isSuperadmin: { type: Boolean, default: false },
  currentUserId: { type: [Number, String], default: null },
  statusMessage: { type: String, default: '' },
  paginationHtml: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      index: '#',
      create: '#',
      systemAccess: '#',
      otpLogs: '#',
    }),
  },
});

const localStatusMessage = ref(props.statusMessage);
const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const filterFormRef = ref(null);

// Modal state management
const isDeleteModalOpen = ref(false);
const selectedUserForDelete = ref(null);

const openDeleteModal = (user) => {
  selectedUserForDelete.value = user;
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  selectedUserForDelete.value = null;
};

// Sort users: Unactivated/pending users first, then by newest account ID
const sortedUsers = computed(() => {
  return [...props.users].sort((a, b) => {
    if (a.is_active === b.is_active) {
      return (b.id || 0) - (a.id || 0);
    }
    return a.is_active ? 1 : -1;
  });
});

const hasActiveFilters = computed(() => {
  return !!(props.filters.search || props.filters.role);
});

const submitFilter = () => {
  if (filterFormRef.value) {
    filterFormRef.value.submit();
  }
};
</script>