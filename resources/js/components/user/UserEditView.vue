<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Account Configuration</h2>
    <p class="text-xs text-slate-500 mt-1 mb-6">Modify account details, adjust RBAC permissions, or reset credentials.</p>

    <!-- Error Summary Alert -->
    <div v-if="errorsList && errorsList.length > 0" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 shadow-sm">
      <div class="flex items-center gap-2 mb-1 font-bold text-rose-800">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Please resolve the following errors:</span>
      </div>
      <ul class="list-disc list-inside space-y-0.5">
        <li v-for="(err, idx) in errorsList" :key="idx">{{ err }}</li>
      </ul>
    </div>

    <form 
      ref="formRef" 
      method="POST" 
      :action="routes.update" 
      @submit.prevent="openUpdateModal" 
      class="space-y-6"
    >
      <input type="hidden" name="_token" :value="csrfToken">
      <input type="hidden" name="_method" value="PUT">

      <!-- Employee ID & Role Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="employee_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Employee ID</label>
          <input 
            type="text" 
            name="employee_id" 
            id="employee_id" 
            v-model="form.employee_id"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono"
          >
          <span v-if="errors.employee_id" class="text-xs text-rose-500 mt-1 block">{{ errors.employee_id[0] }}</span>
        </div>

        <div>
          <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">System Role *</label>
          <select 
            name="role" 
            id="role" 
            v-model="form.role" 
            required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition bg-white"
          >
            <option value="Employee">Employee</option>
            <option value="Admin">Admin</option>
            <option v-if="isSuperadmin" value="Superadmin">Superadmin</option>
          </select>
          <span v-if="errors.role" class="text-xs text-rose-500 mt-1 block">{{ errors.role[0] }}</span>
        </div>
      </div>

      <!-- Full Name -->
      <div>
        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name *</label>
        <input 
          type="text" 
          name="name" 
          id="name" 
          v-model="form.name" 
          required
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
        >
        <span v-if="errors.name" class="text-xs text-rose-500 mt-1 block">{{ errors.name[0] }}</span>
      </div>

      <!-- Email Address -->
      <div>
        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address *</label>
        <input 
          type="email" 
          name="email" 
          id="email" 
          v-model="form.email" 
          required
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono"
        >
        <span v-if="errors.email" class="text-xs text-rose-500 mt-1 block">{{ errors.email[0] }}</span>
      </div>

      <!-- Reset Password Block -->
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
        <div>
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Reset Account Password</h4>
          <p class="text-[11px] text-slate-500">Leave password fields blank if you do not want to alter current credentials.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <input 
              type="password" 
              name="password" 
              id="password"
              v-model="form.password"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition bg-white"
              placeholder="New password"
            >
            <span v-if="errors.password" class="text-xs text-rose-500 mt-1 block">{{ errors.password[0] }}</span>
          </div>

          <div>
            <input 
              type="password" 
              name="password_confirmation" 
              id="password_confirmation"
              v-model="form.password_confirmation"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition bg-white"
              placeholder="Confirm new password"
            >
          </div>
        </div>
      </div>

      <!-- Active Status -->
      <div>
        <label class="inline-flex items-center gap-3 cursor-pointer">
          <input 
            type="checkbox" 
            name="is_active" 
            value="1" 
            v-model="form.is_active"
            class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer"
          >
          <span class="text-xs font-bold text-slate-800">Account Active (Permit Login)</span>
        </label>
      </div>

      <!-- System Access Grants Section -->
      <div class="pt-6 border-t border-slate-200">
        <div class="mb-4">
          <h3 class="text-sm font-bold text-slate-900">System Access Grants</h3>
          <p class="text-xs text-slate-500 mt-0.5">Specify platform access permissions and subsystem roles for this user account.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div 
            v-for="system in systems" 
            :key="system.id" 
            class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-3"
          >
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="h-6 w-auto object-contain">
                <span v-else class="font-bold text-xs text-slate-800">{{ system.name }}</span>
              </div>

              <label class="inline-flex items-center gap-2 cursor-pointer">
                <input 
                  type="checkbox" 
                  :name="`systems[${system.id}][has_access]`" 
                  value="1" 
                  v-model="systemGrants[system.id].has_access"
                  class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand"
                >
                <span class="text-xs font-bold text-slate-700">Grant Access</span>
              </label>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-200/60">
              <span class="text-[11px] font-semibold text-slate-500 uppercase">Subsystem Role:</span>
              <select 
                :name="`systems[${system.id}][role]`" 
                v-model="systemGrants[system.id].role"
                class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 bg-white outline-none focus:border-brand"
              >
                <option value="Employee">Employee</option>
                <option value="Admin">Admin</option>
                <option v-if="isSuperadmin" value="Superadmin">Superadmin</option>
                <option value="None">None</option>
              </select>
            </div>
          </div>

          <p v-if="systems.length === 0" class="text-xs text-slate-400 italic col-span-full">No active systems available to grant access.</p>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="border-t border-slate-100 pt-6 flex items-center justify-end gap-3">
        <a 
          :href="routes.cancel || routes.index || '#'" 
          class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 cursor-pointer"
        >
          Cancel
        </a>
        <button 
          type="submit" 
          class="px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm"
        >
          Update User Account
        </button>
      </div>
    </form>

    <!-- Custom Update Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isUpdateModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeUpdateModal"
      >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Confirm Account Updates</h3>
              <p class="text-xs text-slate-500">Please review user configuration changes before saving.</p>
            </div>
          </div>

          <!-- Changes Preview Summary Card -->
          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">User Name:</span>
              <span class="font-bold text-slate-900">{{ form.name }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Email:</span>
              <span class="font-mono text-slate-800 text-[11px]">{{ form.email }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">System Role:</span>
              <span class="font-bold text-slate-900">{{ form.role }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Account Status:</span>
              <span 
                :class="form.is_active ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-800 bg-amber-100 border-amber-300'"
                class="px-2 py-0.5 rounded-md font-bold text-[10px] border"
              >
                {{ form.is_active ? 'Active' : 'Disabled / Inactive' }}
              </span>
            </div>
            <div v-if="form.password" class="flex justify-between items-center pt-1.5 border-t border-slate-200 text-rose-600 font-semibold">
              <span class="text-slate-500">Password Status:</span>
              <span class="text-[11px] font-bold">New password will be set</span>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to save these updates for 
            <strong class="text-slate-900 font-bold">{{ form.name }}</strong>?
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeUpdateModal" 
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <button 
              type="button" 
              @click="confirmAndSubmit"
              class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Confirm & Save</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
    default: () => ({
      id: '',
      employee_id: '',
      name: '',
      email: '',
      role: 'Employee',
      is_active: true,
      systems: {},
    }),
  },
  systems: {
    type: Array,
    default: () => [],
  },
  isSuperadmin: {
    type: Boolean,
    default: false,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  errorsList: {
    type: Array,
    default: () => [],
  },
  csrfToken: {
    type: String,
    required: true,
  },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      update: '#',
      cancel: '#',
      index: '#',
    }),
  },
});

const formRef = ref(null);
const isUpdateModalOpen = ref(false);

const openUpdateModal = () => {
  isUpdateModalOpen.value = true;
};

const closeUpdateModal = () => {
  isUpdateModalOpen.value = false;
};

const confirmAndSubmit = () => {
  if (formRef.value) {
    formRef.value.submit();
  }
};

const form = ref({
  employee_id: props.user.employee_id || '',
  role: props.user.role || 'Employee',
  name: props.user.name || '',
  email: props.user.email || '',
  password: '',
  password_confirmation: '',
  is_active: !!props.user.is_active,
});

const systemGrants = reactive({});
props.systems.forEach((sys) => {
  const userGrant = props.user.systems?.[sys.id];
  systemGrants[sys.id] = {
    has_access: userGrant ? !!userGrant.has_access : !!sys.has_access,
    role: userGrant ? userGrant.role : (sys.role || 'Employee'),
  };
});
</script>