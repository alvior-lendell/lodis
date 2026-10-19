<template>
  <div class="space-y-6">
    <!-- Status Toast Alert -->
    <div 
      v-if="localStatusMessage" 
      class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-sm"
    >
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ localStatusMessage }}</span>
      </div>
      <button type="button" @click="localStatusMessage = ''" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1">&times;</button>
    </div>

    <!-- Error Summary Alert -->
    <div v-if="errorsList && errorsList.length > 0" class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs font-medium text-rose-700 shadow-sm">
      <ul class="list-disc list-inside space-y-1">
        <li v-for="(error, idx) in errorsList" :key="idx">{{ error }}</li>
      </ul>
    </div>

    <!-- Action Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">User System Access Directory</h2>
        <p class="text-xs text-slate-500 mt-1">Configure individual user subsystem permissions and role-based access across connected platforms.</p>
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto">
        <a 
          :href="routes.index" 
          class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          <span>Back to User Directory</span>
        </a>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="w-full sm:flex-1">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search by employee name, email, or ID..." 
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition"
        >
      </div>

      <div class="w-full sm:w-56">
        <select 
          v-model="selectedSystemFilter" 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium outline-none transition bg-white"
        >
          <option value="">All Systems</option>
          <option v-for="system in systems" :key="system.id" :value="system.id">
            {{ system.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- User Access Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
              <th class="py-4 px-6">User Account</th>
              <th class="py-4 px-6">System Role</th>
              <th class="py-4 px-6">Subsystem Grants & Access Roles</th>
              <th class="py-4 px-6 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr 
              v-for="user in filteredUsers" 
              :key="user.id" 
              class="hover:bg-slate-50/50 transition"
            >
              <!-- User Information -->
              <td class="py-4 px-6">
                <div class="font-bold text-slate-900 text-sm">{{ user.name }}</div>
                <div class="flex items-center gap-2 text-slate-500 text-[11px] font-mono mt-0.5">
                  <span>{{ user.email }}</span>
                  <span v-if="user.employee_id" class="text-slate-400">• ID: {{ user.employee_id }}</span>
                </div>
              </td>

              <!-- System Role -->
              <td class="py-4 px-6 whitespace-nowrap">
                <span v-if="user.role === 'Superadmin'" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Superadmin</span>
                <span v-else-if="user.role === 'Admin'" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Admin</span>
                <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Employee</span>
              </td>

              <!-- Subsystem Access Grants Matrix -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-2 flex-wrap">
                  <div 
                    v-for="system in systems" 
                    :key="system.id" 
                    class="px-3 py-1.5 rounded-xl border flex items-center gap-2 transition"
                    :class="getUserGrant(user, system.id).has_access 
                      ? 'bg-slate-50 border-slate-200 text-slate-800' 
                      : 'bg-slate-100/50 border-slate-200/60 text-slate-400 opacity-60'"
                  >
                    <span 
                      class="w-2 h-2 rounded-full" 
                      :class="getUserGrant(user, system.id).has_access ? 'bg-emerald-500' : 'bg-slate-300'"
                    ></span>
                    
                    <span class="font-bold text-[11px]">{{ system.name }}</span>
                    
                    <span 
                      v-if="getUserGrant(user, system.id).has_access" 
                      class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-semibold"
                    >
                      {{ getUserGrant(user, system.id).role }}
                    </span>
                  </div>

                  <span v-if="systems.length === 0" class="text-slate-400 italic text-[11px]">No active systems available.</span>
                </div>
              </td>

              <!-- Actions -->
              <td class="py-4 px-6 text-right whitespace-nowrap">
                <button 
                  type="button" 
                  @click="openEditModal(user)" 
                  class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-1.5 ml-auto"
                >
                  <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  <span>Manage Access</span>
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredUsers.length === 0">
              <td colspan="4" class="py-12 text-center text-slate-500 font-semibold">
                No user accounts matched your search parameters.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit Access Grants Modal -->
    <Teleport to="body">
      <div 
        v-if="isEditModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeEditModal"
      >
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h3 class="text-lg font-extrabold text-slate-900">Manage System Access</h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Grant subsystem permissions and specify access roles for 
                <strong class="text-slate-800">{{ selectedUser?.name }}</strong>.
              </p>
            </div>
            <button type="button" @click="closeEditModal" class="text-slate-400 hover:text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form ref="accessFormRef" method="POST" :action="selectedUser?.update_access_url || routes.update" @submit.prevent="openConfirmSaveModal" class="space-y-6">
            <input type="hidden" name="_token" :value="csrfToken">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="user_id" :value="selectedUser?.id">

            <div class="grid grid-cols-1 gap-4">
              <div 
                v-for="system in systems" 
                :key="system.id" 
                class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center p-1.5 shrink-0">
                    <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-full max-w-full object-contain">
                    <span v-else class="font-extrabold text-xs text-slate-700">{{ system.name.charAt(0) }}</span>
                  </div>
                  <div>
                    <h4 class="text-xs font-extrabold text-slate-900">{{ system.name }}</h4>
                    <p v-if="system.code" class="text-[10px] font-mono text-slate-400">{{ system.code }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-0 pt-2 sm:pt-0 border-slate-200/60">
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input 
                      type="checkbox" 
                      :name="`systems[${system.id}][has_access]`" 
                      value="1" 
                      v-model="editingGrants[system.id].has_access"
                      class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand"
                    >
                    <span class="text-xs font-bold text-slate-700">Grant Access</span>
                  </label>

                  <select 
                    :name="`systems[${system.id}][role]`" 
                    v-model="editingGrants[system.id].role"
                    :disabled="!editingGrants[system.id].has_access"
                    class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white outline-none focus:border-brand disabled:bg-slate-100 disabled:opacity-60"
                  >
                    <option value="Employee">Employee</option>
                    <option value="Admin">Admin</option>
                    <option v-if="isSuperadmin" value="Superadmin">Superadmin</option>
                    <option value="None">None</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button 
                type="button" 
                @click="closeEditModal" 
                class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
              >
                Cancel
              </button>

              <button 
                type="submit" 
                class="px-5 py-2.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Save Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isConfirmModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="isConfirmModalOpen = false"
      >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Confirm Access Updates</h3>
              <p class="text-xs text-slate-500">Apply new system permissions for this account.</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to update system access grants for 
            <strong class="text-slate-900 font-bold">{{ selectedUser?.name }}</strong>?
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="isConfirmModalOpen = false" 
              class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <button 
              type="button" 
              @click="confirmAndSubmit"
              class="px-4 py-2.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Confirm & Apply</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

const props = defineProps({
  users: { type: Array, default: () => [] },
  systems: { type: Array, default: () => [] },
  isSuperadmin: { type: Boolean, default: false },
  statusMessage: { type: String, default: '' },
  errorsList: { type: Array, default: () => [] },
  csrfToken: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      index: '#',
      update: '#',
    }),
  },
});

const localStatusMessage = ref(props.statusMessage);
const searchQuery = ref('');
const selectedSystemFilter = ref('');

const isEditModalOpen = ref(false);
const isConfirmModalOpen = ref(false);
const selectedUser = ref(null);
const accessFormRef = ref(null);
const editingGrants = reactive({});

// Helper to extract access info for a specific system from a user object
const getUserGrant = (user, systemId) => {
  const grant = user.systems?.[systemId];
  return {
    has_access: grant ? !!grant.has_access : false,
    role: grant ? grant.role : 'None',
  };
};

// Filter users based on search term & selected system access
const filteredUsers = computed(() => {
  return props.users.filter((user) => {
    const matchesSearch = 
      !searchQuery.value ||
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (user.employee_id && user.employee_id.toLowerCase().includes(searchQuery.value.toLowerCase()));

    const matchesSystem = 
      !selectedSystemFilter.value || 
      getUserGrant(user, selectedSystemFilter.value).has_access;

    return matchesSearch && matchesSystem;
  });
});

const openEditModal = (user) => {
  selectedUser.value = user;

  // Initialize form reactive state with existing grants
  props.systems.forEach((sys) => {
    const current = getUserGrant(user, sys.id);
    editingGrants[sys.id] = {
      has_access: current.has_access,
      role: current.role !== 'None' ? current.role : 'Employee',
    };
  });

  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  selectedUser.value = null;
};

const openConfirmSaveModal = () => {
  isConfirmModalOpen.value = true;
};

const confirmAndSubmit = () => {
  if (accessFormRef.value) {
    accessFormRef.value.submit();
  }
};
</script>