<template>
  <div class="space-y-6">
    <!-- Password Rotation Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
      <div class="border-b border-slate-100 pb-4">
        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Rotate Account Password</h2>
        <p class="text-xs text-slate-500 mt-0.5">Maintain security by ensuring your password satisfies system requirements.</p>
      </div>

      <!-- Inline AJAX Response Alerts -->
      <div v-if="pwdAlert.type" class="p-4 rounded-2xl text-xs flex items-center justify-between shadow-sm" :class="pwdAlert.type === 'success' ? 'bg-emerald-50 border border-emerald-200/80 text-emerald-800' : 'bg-rose-50 border border-rose-200/80 text-rose-700'">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 shrink-0" :class="pwdAlert.type === 'success' ? 'text-emerald-600' : 'text-rose-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <span v-if="pwdAlert.title" class="font-bold block mb-1">{{ pwdAlert.title }}</span>
            <span v-if="pwdAlert.message" class="font-medium">{{ pwdAlert.message }}</span>
            <ul v-if="pwdAlert.errors && pwdAlert.errors.length" class="list-disc list-inside space-y-1 font-medium pl-1 mt-1">
              <li v-for="(e, i) in pwdAlert.errors" :key="i">{{ e }}</li>
            </ul>
          </div>
        </div>
        <button type="button" @click="pwdAlert = {}" class="text-slate-400 hover:text-slate-700 text-sm font-bold p-1">&times;</button>
      </div>

      <form v-if="updatePasswordRoute" @submit.prevent="submitPasswordForm" autocomplete="off" class="space-y-5 max-w-xl">
        <div>
          <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Current Password</label>
          <div class="relative rounded-2xl shadow-sm">
            <input 
              id="current_password" 
              v-model="pwdForm.current_password" 
              @input="pwdForm.current_password = $event.target.value"
              :type="showPass.current ? 'text' : 'password'" 
              required 
              autocomplete="current-password"
              class="form-input block w-full pl-4 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition"
              placeholder="••••••••"
            >
            <button type="button" @click="showPass.current = !showPass.current" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <div>
          <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password</label>
          <div class="relative rounded-2xl shadow-sm">
            <input 
              id="password" 
              v-model="pwdForm.password" 
              @input="pwdForm.password = $event.target.value"
              :type="showPass.new ? 'text' : 'password'" 
              required 
              autocomplete="new-password"
              class="form-input block w-full pl-4 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition"
              placeholder="••••••••"
            >
            <button type="button" @click="showPass.new = !showPass.new" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <div>
          <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm New Password</label>
          <div class="relative rounded-2xl shadow-sm">
            <input 
              id="password_confirmation" 
              v-model="pwdForm.password_confirmation" 
              @input="pwdForm.password_confirmation = $event.target.value"
              :type="showPass.confirm ? 'text' : 'password'" 
              required 
              autocomplete="new-password"
              class="form-input block w-full pl-4 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm font-medium outline-none transition"
              placeholder="••••••••"
            >
            <button type="button" @click="showPass.confirm = !showPass.confirm" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <!-- Security Checklist Requirements -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-2.5">
          <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">Security Policy Requirements</span>
          
          <div class="flex items-center gap-2 transition" :class="pwdChecklist.minLen ? 'text-emerald-600 font-medium' : 'text-slate-400'">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Minimum {{ securitySettings?.min_password_length || 8 }} characters</span>
          </div>

          <div v-if="securitySettings?.require_uppercase" class="flex items-center gap-2 transition" :class="pwdChecklist.uppercase ? 'text-emerald-600 font-medium' : 'text-slate-400'">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>At least one uppercase character (A-Z)</span>
          </div>

          <div v-if="securitySettings?.require_numeric" class="flex items-center gap-2 transition" :class="pwdChecklist.numeric ? 'text-emerald-600 font-medium' : 'text-slate-400'">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>At least one numeric digit (0-9)</span>
          </div>

          <div v-if="securitySettings?.require_special_char" class="flex items-center gap-2 transition" :class="pwdChecklist.special ? 'text-emerald-600 font-medium' : 'text-slate-400'">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>At least one special character (!@#$%^&*)</span>
          </div>

          <div class="flex items-center gap-2 transition" :class="pwdChecklist.match ? 'text-emerald-600 font-medium' : 'text-slate-400'">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Passwords match</span>
          </div>
        </div>

        <button 
          type="submit" 
          :disabled="!isPwdFormValid || pwdUpdating"
          class="px-6 py-3.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="pwdUpdating" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ pwdUpdating ? 'Updating...' : 'Update Password' }}</span>
        </button>
      </form>
    </div>

    <!-- Password History Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
        <div>
          <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Password Change History</h2>
          <p class="text-xs text-slate-500 mt-0.5">Historical records of past password modification and rotation events.</p>
        </div>
        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 w-fit">
          Reuse Protection: Prevents Last {{ securitySettings?.prevent_password_reuse_count || 5 }} Passwords
        </span>
      </div>

      <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <th class="p-4">Action Event</th>
              <th class="p-4">IP Address</th>
              <th class="p-4">Timestamp</th>
              <th class="p-4 text-right">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
            <tr v-for="(history, idx) in localPasswordHistories" :key="idx" class="hover:bg-slate-50/50 transition">
              <td class="p-4 font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>{{ history.headline_event }}</span>
              </td>
              <td class="p-4 font-mono text-slate-500">{{ history.ip_address || '127.0.0.1' }}</td>
              <td class="p-4 text-slate-500">
                {{ history.created_at_formatted }}
                <span v-if="history.created_at_human" class="text-slate-400 text-[11px]">({{ history.created_at_human }})</span>
              </td>
              <td class="p-4 text-right">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                  Recorded
                </span>
              </td>
            </tr>
            <tr v-if="passwordHistories.length === 0">
              <td colspan="4" class="p-8 text-center text-slate-500 text-xs">
                No previous password change history recorded yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  securitySettings: { type: Object, default: () => ({}) },
  passwordHistories: { type: Array, default: () => [] },
  csrfToken: { type: String, required: true },
  updatePasswordRoute: { type: String, default: '' },
});

const showPass = ref({ current: false, new: false, confirm: false });
const pwdUpdating = ref(false);
const pwdAlert = ref({});
const pwdForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const pwdChecklist = computed(() => {
  const pwd = pwdForm.value.password;
  const confirm = pwdForm.value.password_confirmation;
  const minLen = props.securitySettings?.min_password_length || 8;

  return {
    minLen: pwd.length >= minLen,
    uppercase: !props.securitySettings?.require_uppercase || /[A-Z]/.test(pwd),
    numeric: !props.securitySettings?.require_numeric || /[0-9]/.test(pwd),
    special: !props.securitySettings?.require_special_char || /[^A-Za-z0-9]/.test(pwd),
    match: pwd.length > 0 && pwd === confirm,
  };
});

const isPwdFormValid = computed(() => {
  const c = pwdChecklist.value;
  return c.minLen && c.uppercase && c.numeric && c.special && c.match;
});

const localPasswordHistories = ref([...props.passwordHistories]);

const submitPasswordForm = async () => {
  // Sync DOM elements directly to handle browser autofill
  const currentEl = document.getElementById('current_password');
  const newEl = document.getElementById('password');
  const confirmEl = document.getElementById('password_confirmation');

  const currentVal = currentEl ? currentEl.value : pwdForm.value.current_password;
  const newVal = newEl ? newEl.value : pwdForm.value.password;
  const confirmVal = confirmEl ? confirmEl.value : pwdForm.value.password_confirmation;

  pwdUpdating.value = true;
  pwdAlert.value = {};

  try {
    const response = await fetch(props.updatePasswordRoute, {
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': props.csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        current_password: currentVal,
        password: newVal,
        password_confirmation: confirmVal,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      pwdAlert.value = { type: 'success', message: 'Password updated successfully.' };
      pwdForm.value = { current_password: '', password: '', password_confirmation: '' };
      if (currentEl) currentEl.value = '';
      if (newEl) newEl.value = '';
      if (confirmEl) confirmEl.value = '';
    } else if (response.status === 422) {
      pwdAlert.value = {
        type: 'error',
        title: 'Please resolve the following errors:',
        errors: data.errors ? Object.values(data.errors).flat() : [data.message],
      };
    }
  } catch (err) {
    pwdAlert.value = { type: 'error', message: err.message || 'Failed to update password.' };
  } finally {
    pwdUpdating.value = false;
  }
};
</script>