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

    <!-- Email Address -->
    <div>
      <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
        Company Email Address
      </label>
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
          @input="handleEmailInput"
          @blur="performEmailCheck"
          @keydown.enter.prevent="performEmailCheck"
          :readonly="isSubmitting"
          required 
          autofocus 
          autocomplete="off"
          class="form-input block w-full pl-11 pr-10 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400"
          placeholder="email@lendell.ph"
        >
        <div v-if="isEmailChecking" class="absolute right-3.5 top-4">
          <svg class="animate-spin h-5 w-5 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
      <p v-if="emailFeedback" class="text-xs mt-1.5 font-medium" :class="emailFeedbackClass">
        {{ emailFeedback }}
      </p>
    </div>

    <!-- Password Input -->
    <div>
      <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password</label>
      <div class="relative rounded-2xl shadow-sm">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <input 
          id="password" 
          :type="showPassword ? 'text' : 'password'" 
          name="password" 
          v-model="form.password"
          :disabled="!isEmailVerified"
          @keyup="checkCapsLock($event, 'pwd')"
          @keydown="checkCapsLock($event, 'pwd')"
          @blur="isCapsLockPwd = false"
          required 
          autocomplete="new-password"
          class="form-input block w-full pl-11 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400 disabled:bg-slate-100 disabled:cursor-not-allowed"
          placeholder="••••••••"
        >
        <button 
          type="button" 
          @click="showPassword = !showPassword" 
          title="Toggle password visibility"
          class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand focus:text-brand outline-none transition z-10"
        >
          <svg v-if="!showPassword" class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          <svg v-else class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
          </svg>
        </button>
      </div>

      <div v-if="isCapsLockPwd" class="text-[11px] font-bold text-amber-600 mt-1.5 flex items-center gap-1.5 animate-pulse">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>Caps Lock is turned ON</span>
      </div>
    </div>

    <!-- Confirm Password Input -->
    <div>
      <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm Password</label>
      <div class="relative rounded-2xl shadow-sm">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <input 
          id="password_confirmation" 
          :type="showConfirmPassword ? 'text' : 'password'" 
          name="password_confirmation" 
          v-model="form.passwordConfirmation"
          :disabled="!isEmailVerified"
          @keyup="checkCapsLock($event, 'confirm')"
          @keydown="checkCapsLock($event, 'confirm')"
          @blur="isCapsLockConfirm = false"
          required 
          autocomplete="new-password"
          class="form-input block w-full pl-11 pr-11 py-3.5 rounded-2xl border-slate-200 focus:border-brand focus:ring-4 focus:ring-brand/15 text-sm outline-none transition font-medium placeholder:text-slate-400 disabled:bg-slate-100 disabled:cursor-not-allowed"
          placeholder="••••••••"
        >
        <button 
          type="button" 
          @click="showConfirmPassword = !showConfirmPassword" 
          title="Toggle password confirmation visibility"
          class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand focus:text-brand outline-none transition z-10"
        >
          <svg v-if="!showConfirmPassword" class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          <svg v-else class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
          </svg>
        </button>
      </div>

      <div v-if="isCapsLockConfirm" class="text-[11px] font-bold text-amber-600 mt-1.5 flex items-center gap-1.5 animate-pulse">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>Caps Lock is turned ON</span>
      </div>
    </div>

    <!-- Password Requirements Checklist -->
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-2.5 shadow-sm">
      <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">Password Policy</span>
      
      <div class="flex items-center gap-2 transition" :class="ruleLength ? 'text-emerald-600 font-medium' : 'text-slate-400'">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>At least {{ securitySettings.minPasswordLength }} characters long</span>
      </div>

      <div v-if="securitySettings.requireUppercase" class="flex items-center gap-2 transition" :class="ruleUppercase ? 'text-emerald-600 font-medium' : 'text-slate-400'">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>Includes at least one uppercase letter (A-Z)</span>
      </div>

      <div v-if="securitySettings.requireNumeric" class="flex items-center gap-2 transition" :class="ruleNumeric ? 'text-emerald-600 font-medium' : 'text-slate-400'">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>Includes at least one number (0-9)</span>
      </div>

      <div v-if="securitySettings.requireSpecialChar" class="flex items-center gap-2 transition" :class="ruleSpecial ? 'text-emerald-600 font-medium' : 'text-slate-400'">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>Includes at least one special character (!@#$%^&*)</span>
      </div>

      <div class="flex items-center gap-2 transition" :class="ruleMatch ? 'text-emerald-600 font-medium' : 'text-slate-400'">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>Passwords match</span>
      </div>
    </div>

    <!-- Submit Button -->
    <button 
      type="submit" 
      :disabled="!isFormValid || isSubmitting"
      class="w-full py-3.5 px-4 rounded-2xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-sm font-bold shadow-lg shadow-brand/20 hover:shadow-xl hover:shadow-brand/30 transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span>{{ isSubmitting ? 'Sending OTP Code...' : 'Send Verification OTP' }}</span>
      <svg v-if="!isSubmitting" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
      </svg>
    </button>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  csrfToken: { type: String, required: true },
  oldEmail: { type: String, default: '' },
  securitySettings: {
    type: Object,
    default: () => ({
      minPasswordLength: 8,
      requireUppercase: false,
      requireNumeric: false,
      requireSpecialChar: false,
    }),
  },
});

const form = ref({
  email: props.oldEmail,
  password: '',
  passwordConfirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isCapsLockPwd = ref(false);
const isCapsLockConfirm = ref(false);

const isEmailChecking = ref(false);
const isEmailVerified = ref(false);
const emailFeedback = ref('');
const emailFeedbackClass = ref('');
const isSubmitting = ref(false);

let lookupTimeout = null;

const checkCapsLock = (event, target) => {
  const isCaps = event.getModifierState && event.getModifierState('CapsLock');
  if (target === 'pwd') isCapsLockPwd.value = !!isCaps;
  if (target === 'confirm') isCapsLockConfirm.value = !!isCaps;
};

const resetFormState = () => {
  isEmailVerified.value = false;
  form.value.password = '';
  form.value.passwordConfirmation = '';
  emailFeedback.value = '';
  emailFeedbackClass.value = '';
};

const handleEmailInput = () => {
  clearTimeout(lookupTimeout);
  lookupTimeout = setTimeout(performEmailCheck, 500);
};

const performEmailCheck = async () => {
  const email = form.value.email.trim();
  if (!email || !/\S+@\S+\.\S+/.test(email)) {
    resetFormState();
    return;
  }

  isEmailChecking.value = true;
  emailFeedback.value = '';

  try {
    const response = await fetch('/register/check-email', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email }),
    });

    if (!response.ok) throw new Error(`Status ${response.status}`);

    const data = await response.json();
    isEmailChecking.value = false;

    if (data.found && !data.has_account) {
      isEmailVerified.value = true;
      emailFeedback.value = '✓ Verified employee email. Please set your password.';
      emailFeedbackClass.value = 'text-emerald-600 block';
    } else {
      resetFormState();
      emailFeedback.value = data.message || 'Email lookup failed.';
      emailFeedbackClass.value = 'text-rose-600 block';
    }
  } catch (err) {
    isEmailChecking.value = false;
    resetFormState();
    emailFeedback.value = 'Unable to verify email address. Please check connection.';
    emailFeedbackClass.value = 'text-rose-600 block';
  }
};

// Password Checklist Rules
const ruleLength = computed(() => form.value.password.length >= props.securitySettings.minPasswordLength);
const ruleUppercase = computed(() => !props.securitySettings.requireUppercase || /[A-Z]/.test(form.value.password));
const ruleNumeric = computed(() => !props.securitySettings.requireNumeric || /[0-9]/.test(form.value.password));
const ruleSpecial = computed(() => !props.securitySettings.requireSpecialChar || /[^A-Za-z0-9]/.test(form.value.password));
const ruleMatch = computed(() => form.value.password.length > 0 && form.value.password === form.value.passwordConfirmation);

const isPasswordValid = computed(() => {
  return ruleLength.value && ruleUppercase.value && ruleNumeric.value && ruleSpecial.value && ruleMatch.value;
});

const isFormValid = computed(() => isEmailVerified.value && isPasswordValid.value);

const handleSubmit = () => {
  if (isFormValid.value) {
    isSubmitting.value = true;
  }
};
</script>