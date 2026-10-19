<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Provision User Account</h2>
    <p class="text-xs text-slate-500 mt-1 mb-6">Create a new authenticated account for system access and module privileges.</p>

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

    <form method="POST" :action="routes.store" class="space-y-6">
      <input type="hidden" name="_token" :value="csrfToken">

      <!-- Employee Selector Dropdown -->
      <div>
        <label for="employee_lookup" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          Select Employee Record <span class="text-rose-500">*</span>
        </label>
        <select 
          id="employee_lookup"
          v-model="selectedEmployeeId"
          @change="onEmployeeSelect($event.target.value)"
          class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 text-xs font-medium outline-none focus:border-brand bg-white"
        >
          <option value="">-- Choose Employee --</option>
          <option v-for="emp in employees" :key="emp.id" :value="emp.employee_id">
            [{{ emp.employee_id }}] {{ emp.full_name }} — {{ emp.department }}
          </option>
        </select>
      </div>

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
            placeholder="e.g. EMP-0012"
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
          placeholder="First and Last Name"
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
          placeholder="user@lendell-local.xyz"
        >
        <span v-if="errors.email" class="text-xs text-rose-500 mt-1 block">{{ errors.email[0] }}</span>
      </div>

      <!-- Password Fields Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password *</label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            v-model="form.password" 
            required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
            placeholder="Minimum 8 characters"
          >
          <span v-if="errors.password" class="text-xs text-rose-500 mt-1 block">{{ errors.password[0] }}</span>
        </div>

        <div>
          <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm Password *</label>
          <input 
            type="password" 
            name="password_confirmation" 
            id="password_confirmation" 
            v-model="form.password_confirmation" 
            required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
            placeholder="Re-type password"
          >
        </div>
      </div>

      <!-- Active Status -->
      <div class="pt-2">
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
          :href="routes.index || '#'" 
          class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200"
        >
          Cancel
        </a>
        <button 
          type="submit" 
          class="px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm"
        >
          Create User Account
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const props = defineProps({
  employees: { 
    type: Array, 
    default: () => [] 
  },
  oldValues: {
    type: Object,
    default: () => ({}),
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
      store: '#',
      index: '#',
    }),
  },
});

const selectedEmployeeId = ref(props.oldValues.employee_id || '');
const form = ref({
  employee_id: props.oldValues.employee_id || '',
  role: props.oldValues.role || 'Employee',
  name: props.oldValues.name || '',
  email: props.oldValues.email || '',
  password: '',
  password_confirmation: '',
  is_active: props.oldValues.is_active !== undefined ? !!props.oldValues.is_active : true,
});

// Auto-fill Name & Email when an Employee is selected
const onEmployeeSelect = (employeeId) => {
  const emp = props.employees.find(e => e.employee_id === employeeId);
  if (emp) {
    form.value.employee_id = emp.employee_id;
    form.value.name = emp.full_name;
    form.value.email = emp.email || '';
  }
};

const systemGrants = reactive({});
props.systems.forEach((sys) => {
  systemGrants[sys.id] = {
    has_access: !!sys.has_access,
    role: sys.role || 'Employee',
  };
});
</script>