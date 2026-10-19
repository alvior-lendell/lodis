<template>
  <form 
    :action="actionUrl" 
    method="POST" 
    @submit="handleSubmit" 
    class="space-y-4 transition-opacity duration-200"
    :class="{ 'opacity-85 pointer-events-none': isSubmitting }"
  >
    <input type="hidden" name="_token" :value="csrfToken">

    <!-- Email Channel Option -->
    <label 
      class="block p-4 rounded-2xl border cursor-pointer transition flex items-center justify-between"
      :class="formMethod === 'email' ? 'border-brand bg-brand/5 ring-2 ring-brand/15' : 'border-slate-200 hover:border-brand hover:bg-slate-50/50'"
    >
      <div class="flex items-center gap-3.5">
        <div class="p-2.5 rounded-xl bg-slate-100 text-slate-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <span class="block text-xs font-bold text-slate-900">Email OTP Verification</span>
          <span class="block text-xs text-slate-500 font-mono">{{ maskedEmail }}</span>
        </div>
      </div>
      <input type="radio" name="method" value="email" v-model="formMethod" class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
    </label>

    <!-- SMS Channel Option -->
    <label 
      class="block p-4 rounded-2xl border transition flex items-center justify-between"
      :class="!phoneNumber ? 'opacity-50 cursor-not-allowed bg-slate-100 border-slate-200' : (formMethod === 'sms' ? 'border-brand bg-brand/5 ring-2 ring-brand/15' : 'cursor-pointer border-slate-200 hover:border-brand hover:bg-slate-50/50')"
    >
      <div class="flex items-center gap-3.5">
        <div class="p-2.5 rounded-xl bg-slate-100 text-slate-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <span class="block text-xs font-bold text-slate-900">Mobile Number Match (SMS)</span>
          <span class="block text-xs text-slate-500 font-mono">
            {{ phoneNumber ? maskedPhone : 'No phone number linked' }}
          </span>
        </div>
      </div>
      <input type="radio" name="method" value="sms" v-model="formMethod" :disabled="!phoneNumber" class="h-4 w-4 text-brand focus:ring-brand border-slate-300">
    </label>

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
      <span>{{ isSubmitting ? 'Processing...' : 'Continue to Verification' }}</span>
      <svg v-if="!isSubmitting" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </button>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  csrfToken: { type: String, required: true },
  email: { type: String, default: '' },
  phoneNumber: { type: String, default: '' },
  selectedMethod: { type: String, default: 'email' },
});

const formMethod = ref(props.selectedMethod);
const isSubmitting = ref(false);

const maskedEmail = computed(() => {
  if (!props.email) return '';
  const parts = props.email.split('@');
  if (parts.length < 2) return props.email;
  const name = parts[0];
  const domain = parts[1];
  if (name.length <= 3) return `***@${domain}`;
  return `${name.substring(0, 3)}***@${domain}`;
});

const maskedPhone = computed(() => {
  if (!props.phoneNumber) return '';
  if (props.phoneNumber.length <= 6) return '***';
  return `${props.phoneNumber.substring(0, 3)}***${props.phoneNumber.substring(props.phoneNumber.length - 3)}`;
});

const handleSubmit = () => {
  isSubmitting.value = true;
};
</script>