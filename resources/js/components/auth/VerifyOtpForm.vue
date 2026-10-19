<template>
  <div class="space-y-6">
    <!-- Form Header with Switch Method Link -->
    <div class="mb-6 text-center lg:text-left">
      <div class="flex items-center justify-between mb-1">
        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
          {{ headerTitle }}
        </h3>
        <a 
          v-if="switchMethodUrl" 
          :href="switchMethodUrl" 
          class="text-xs font-bold text-brand hover:underline flex items-center gap-1"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
          Change Method
        </a>
      </div>
      <p class="text-xs sm:text-sm text-slate-500">
        {{ headerSubtext }}
      </p>
    </div>

    <!-- Authenticator App Setup Banner -->
    <div v-if="method === 'authenticator'" class="mb-6 p-5 rounded-3xl bg-slate-50 border border-slate-200 space-y-4 text-center">
      <div class="text-left space-y-1">
        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Authenticator Setup</h4>
        <p class="text-xs text-slate-500">Scan this QR code using Google Authenticator, 1Password, or Authy, then enter the code below.</p>
      </div>

      <div v-if="qrCodeUrl" class="p-3 bg-white rounded-2xl inline-block shadow-sm border border-slate-200/80">
        <img :src="qrCodeUrl" alt="Authenticator QR Code" class="w-44 h-44 mx-auto object-contain">
      </div>

      <div v-if="secretKey" class="bg-white p-3 rounded-2xl border border-slate-200 text-center space-y-1">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Manual Key Entry</span>
        <code class="text-xs font-mono font-bold text-brand select-all tracking-wider block">{{ secretKey }}</code>
      </div>
    </div>

    <!-- Channel Info Banner for SMS & Email -->
    <div v-else class="mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 font-medium leading-relaxed">
      <template v-if="method === 'sms'">
        To verify your identity, enter the mobile number linked to your employee record (e.g. <strong>09XXXXXXXXX</strong>).
      </template>
      <template v-else>
        Code dispatched via Email to <strong class="text-slate-800">{{ email }}</strong>.
      </template>
    </div>

    <!-- Verification Form -->
    <form 
      :action="actionUrl" 
      method="POST" 
      autocomplete="off" 
      @submit="handleSubmit" 
      class="space-y-5 transition-opacity duration-200"
      :class="{ 'opacity-85 pointer-events-none': isSubmitting }"
    >
      <input type="hidden" name="_token" :value="csrfToken">

      <div>
        <label for="otp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          {{ inputLabel }}
        </label>
        <div class="relative rounded-2xl shadow-sm">
          <input 
            id="otp" 
            :type="method === 'sms' ? 'tel' : 'text'" 
            name="otp" 
            v-model="otpValue"
            :maxlength="method === 'sms' ? 13 : 6" 
            required 
            autofocus 
            autocomplete="off"
            class="form-input block w-full text-center text-xl sm:text-2xl font-mono tracking-widest px-4 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 outline-none transition font-semibold placeholder:text-slate-300 placeholder:tracking-normal"
            :placeholder="method === 'sms' ? '0917XXXXXXX' : '000000'"
          >
        </div>
      </div>

      <button 
        type="submit" 
        :disabled="isSubmitting"
        class="w-full py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-85 disabled:cursor-not-allowed"
      >
        <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ isSubmitting ? 'Verifying...' : 'Verify & Complete Account' }}</span>
      </button>
    </form>

    <!-- Resend Form with Countdown (Email Only) -->
    <div v-if="method === 'email' && resendActionUrl" class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
      <span>Didn't receive the code?</span>
      <form :action="resendActionUrl" method="POST" @submit="handleResend" autocomplete="off">
        <input type="hidden" name="_token" :value="csrfToken">
        <button 
          type="submit" 
          :disabled="timerSeconds > 0 || isResending" 
          class="font-bold transition flex items-center gap-1.5"
          :class="timerSeconds > 0 || isResending ? 'text-slate-400 cursor-not-allowed opacity-60' : 'text-brand hover:text-brand-hover cursor-pointer'"
        >
          <svg v-if="isResending" class="animate-spin h-3.5 w-3.5 text-brand shrink-0" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>
            {{ isResending ? 'Sending...' : 'Resend Code' }}
            <span v-if="timerSeconds > 0">({{ timerSeconds }}s)</span>
          </span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  resendActionUrl: { type: String, default: '' },
  switchMethodUrl: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  method: { type: String, default: 'email' },
  email: { type: String, default: '' },
  phoneNumber: { type: String, default: '' },
  qrCodeUrl: { type: String, default: '' },
  secretKey: { type: String, default: '' },
  cooldownSeconds: { type: [Number, String], default: 60 },
});

const otpValue = ref('');
const isSubmitting = ref(false);
const isResending = ref(false);
const timerSeconds = ref(parseInt(props.cooldownSeconds, 10) || 60);
let timerInterval = null;

const headerTitle = computed(() => {
  if (props.method === 'sms') return 'Confirm Mobile Number';
  if (props.method === 'authenticator') return 'Authenticator Code';
  return 'Enter Security Code';
});

const headerSubtext = computed(() => {
  if (props.method === 'sms') return 'Enter the mobile phone number registered under your employee profile.';
  return 'Enter the 6-digit verification code to activate your account.';
});

const inputLabel = computed(() => {
  if (props.method === 'authenticator') return '6-Digit Authenticator Code';
  if (props.method === 'sms') return 'Registered Mobile Number';
  return '6-Digit Verification Code';
});

const handleSubmit = () => {
  isSubmitting.value = true;
};

const handleResend = () => {
  isResending.value = true;
};

onMounted(() => {
  if (props.method === 'email' && timerSeconds.value > 0) {
    timerInterval = setInterval(() => {
      if (timerSeconds.value > 0) {
        timerSeconds.value--;
      } else {
        clearInterval(timerInterval);
      }
    }, 1000);
  }
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});
</script>