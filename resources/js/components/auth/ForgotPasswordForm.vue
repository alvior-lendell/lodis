<template>
  <form 
    :action="actionUrl" 
    method="POST" 
    autocomplete="off" 
    @submit="handleSubmit" 
    class="space-y-5 transition-opacity duration-200" 
    :class="{ 'opacity-85 pointer-events-none': isSubmitting }"
  >
    <input type="hidden" name="_token" :value="csrfToken">

    <!-- Registered Email Input -->
    <div>
      <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Registered Email Address</label>
      <div class="relative rounded-2xl shadow-sm">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <input 
          id="email" 
          type="email" 
          name="email" 
          v-model="form.email"
          :readonly="isSubmitting"
          required 
          autofocus 
          autocomplete="off"
          class="form-input block w-full pl-11 pr-4 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400"
          placeholder="email@lendell.ph"
        >
      </div>
    </div>

    <!-- Verification Channel Selection -->
    <div class="space-y-3 pt-2">
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Select Reset Channel</label>

      <!-- Email Option -->
      <label 
        class="block p-3.5 rounded-2xl border cursor-pointer transition flex items-center justify-between"
        :class="form.method === 'email' ? 'border-brand bg-brand/5 ring-2 ring-brand/15' : 'border-slate-200 hover:border-brand hover:bg-slate-50/50'"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div>
            <span class="block text-xs font-bold text-slate-900">Email Verification Link</span>
            <span class="block text-[11px] text-slate-500">Receive password reset instructions via inbox</span>
          </div>
        </div>
        <input type="radio" name="method" value="email" v-model="form.method" class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
      </label>

      <!-- SMS Option -->
      <label 
        v-if="hasSmsOption"
        class="block p-3.5 rounded-2xl border cursor-pointer transition flex items-center justify-between"
        :class="form.method === 'sms' ? 'border-brand bg-brand/5 ring-2 ring-brand/15' : 'border-slate-200 hover:border-brand hover:bg-slate-50/50'"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          </div>
          <div>
            <span class="block text-xs font-bold text-slate-900">Mobile Phone Confirmation (SMS)</span>
            <span class="block text-[11px] text-slate-500">Confirm linked contact number to reset</span>
          </div>
        </div>
        <input type="radio" name="method" value="sms" v-model="form.method" class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
      </label>

      <!-- Authenticator App Option -->
      <label 
        class="block p-3.5 rounded-2xl border cursor-pointer transition flex items-center justify-between"
        :class="form.method === 'authenticator' ? 'border-brand bg-brand/5 ring-2 ring-brand/15' : 'border-slate-200 hover:border-brand hover:bg-slate-50/50'"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <span class="block text-xs font-bold text-slate-900">Authenticator App (TOTP)</span>
            <span class="block text-[11px] text-slate-500">Validate code via 2FA authenticator app</span>
          </div>
        </div>
        <input type="radio" name="method" value="authenticator" v-model="form.method" class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
      </label>
    </div>

    <!-- Submit Button -->
    <button 
      type="submit" 
      :disabled="isSubmitting"
      class="w-full mt-6 py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-85 disabled:cursor-not-allowed"
    >
      <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span>{{ isSubmitting ? 'Processing Recovery Request...' : 'Continue Recovery Process' }}</span>
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  csrfToken: { type: String, required: true },
  oldEmail: { type: String, default: '' },
  selectedMethod: { type: String, default: 'email' },
  hasSmsOption: { type: Boolean, default: false },
});

const form = ref({
  email: props.oldEmail,
  method: props.selectedMethod,
});

const isSubmitting = ref(false);

const handleSubmit = () => {
  isSubmitting.value = true;
};
</script>