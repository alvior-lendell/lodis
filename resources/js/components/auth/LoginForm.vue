<template>
  <form 
    :action="actionUrl" 
    method="POST" 
    autocomplete="off" 
    @submit="handleSubmit" 
    class="space-y-5 transition-opacity duration-200" 
    :class="{ 'opacity-85 pointer-events-none': isSubmitting }"
    :style="{ '--brand-color': brandColor, '--brand-hover': brandHoverColor }"
  >
    <input type="hidden" name="_token" :value="csrfToken">

    <!-- Subsystem Dynamic Header -->
  <div class="text-center mb-8">
    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
      Sign in to
    </p>

    <img 
      v-if="systemLogo" 
      :src="systemLogo" 
      :alt="systemName" 
      class="h-10 w-auto mx-auto mb-3 object-contain drop-shadow-sm"
    />
    <h3 v-else class="text-2xl font-extrabold text-slate-900 tracking-tight mb-1">
      {{ systemName }}
    </h3>

    <p class="text-xs text-slate-500 mt-1">
      Enter your credentials to access your portal session.
    </p>
  </div>

    <!-- Employee ID / Email Field -->
    <div>
      <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Employee ID or Email</label>
      <div class="relative rounded-2xl shadow-sm">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <input 
          id="login" 
          type="text" 
          name="login" 
          v-model="form.login"
          :readonly="isSubmitting"
          required 
          autofocus 
          autocomplete="off"
          class="dynamic-input block w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 text-sm outline-none transition font-medium placeholder:text-slate-400"
          placeholder="Employee ID or Email"
        >
      </div>
    </div>

    <!-- Password Field -->
    <div>
      <div class="flex justify-between items-center mb-2">
        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
      </div>
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
          :readonly="isSubmitting"
          @keyup="checkCapsLock"
          @keydown="checkCapsLock"
          @blur="isCapsLockOn = false"
          required 
          autocomplete="current-password"
          class="dynamic-input block w-full pl-11 pr-11 py-3.5 rounded-2xl border border-slate-200 text-sm outline-none transition font-medium placeholder:text-slate-400"
          placeholder="••••••••"
        >

        <!-- Toggle Visibility Button -->
        <button 
          type="button" 
          @click="showPassword = !showPassword" 
          title="Toggle password visibility"
          class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 outline-none transition z-10"
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

      <!-- Caps Lock Warning -->
      <div v-if="isCapsLockOn" class="text-[11px] font-bold text-amber-600 mt-1.5 flex items-center gap-1.5 animate-pulse">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>Caps Lock is turned ON</span>
      </div>
    </div>

    <!-- Keep Me Signed In Switch -->
    <div class="flex items-center justify-between pt-1">
      <label for="remember" class="flex items-center gap-3 cursor-pointer select-none group py-1">
        <div class="relative inline-flex items-center">
          <input 
            type="checkbox" 
            id="remember" 
            name="remember" 
            v-model="form.remember" 
            class="sr-only peer"
          >
          <div 
            class="w-9 h-5 bg-slate-200 rounded-full peer transition-colors duration-200"
            :style="{ backgroundColor: form.remember ? brandColor : '' }"
          ></div>
          <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform duration-200 peer-checked:translate-x-4 shadow-sm pointer-events-none"></div>
        </div>
        <span class="text-xs font-bold text-slate-600 group-hover:text-slate-900 transition">Keep me signed in</span>
      </label>
      <a 
        v-if="forgotPasswordUrl" 
        :href="forgotPasswordUrl" 
        class="dynamic-link text-xs font-bold transition"
      >Forgot Password?</a>
    </div>

    <!-- Turnstile Slot -->
    <slot name="turnstile"></slot>

    <!-- Dynamic Submit Button -->
    <button 
      type="submit" 
      :disabled="isSubmitting"
      class="dynamic-btn w-full py-3.5 px-4 rounded-2xl text-white text-sm font-bold transition duration-200 ease-in-out flex items-center justify-center gap-2 disabled:opacity-85 disabled:cursor-not-allowed"
    >
      <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span>{{ isSubmitting ? 'Authenticating...' : `Authenticate & Sign In` }}</span>
    </button>
  </form>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  csrfToken: { type: String, required: true },
  oldLogin: { type: String, default: '' },
  oldRemember: { type: Boolean, default: false },
  forgotPasswordUrl: { type: String, default: '' },
  systemName: { type: String, default: 'LODISv2' },
  systemLogo: { type: String, default: '' },
  brandColor: { type: String, default: '#00687A' },
  brandHoverColor: { type: String, default: '#005261' },
});

const form = ref({
  login: props.oldLogin,
  password: '',
  remember: props.oldRemember,
});

const showPassword = ref(false);
const isCapsLockOn = ref(false);
const isSubmitting = ref(false);

let widgetId = null;
let turnstileTimer = null;

const renderTurnstile = () => {
  const container = document.getElementById('turnstile-container');
  if (!container || !window.turnstile) return false;

  const sitekey = container.getAttribute('data-sitekey');
  if (!sitekey) return false;

  // Prevent re-rendering if already initialized
  if (widgetId !== null) return true;

  try {
    // Clear any residual nodes before rendering
    container.innerHTML = '';

    widgetId = window.turnstile.render('#turnstile-container', {
      sitekey: sitekey,
      theme: 'light',
      size: 'flexible',
      appearance: 'always',
      'expired-callback': () => {
        if (widgetId !== null && window.turnstile) {
          window.turnstile.reset(widgetId);
        }
      },
    });
    return true;
  } catch (error) {
    console.warn('[Turnstile] Render attempt warning:', error);
    return false;
  }
};

onMounted(() => {
  // 1. Try immediate render if Turnstile is already loaded
  if (renderTurnstile()) return;

  // 2. Bind global callback in case the Cloudflare script finishes loading later
  window.onloadTurnstileCallback = () => {
    renderTurnstile();
  };

  // 3. Fail-safe polling (max 10 seconds)
  let attempts = 0;
  turnstileTimer = setInterval(() => {
    attempts++;
    if (renderTurnstile() || attempts >= 100) {
      clearInterval(turnstileTimer);
    }
  }, 100);
});

onUnmounted(() => {
  if (turnstileTimer) {
    clearInterval(turnstileTimer);
  }
  if (widgetId !== null && window.turnstile) {
    try {
      window.turnstile.remove(widgetId);
    } catch (e) {
      // Ignore cleanup errors on unmount
    }
  }
});

const checkCapsLock = (event) => {
  if (event.getModifierState && event.getModifierState('CapsLock')) {
    isCapsLockOn.value = true;
  } else {
    isCapsLockOn.value = false;
  }
};

const handleSubmit = () => {
  isSubmitting.value = true;
};
</script>

<style scoped>
/* Dynamic Input Focus Border & Glow Ring */
.dynamic-input:focus {
  border-color: var(--brand-color) !important;
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-color) 18%, transparent) !important;
}

/* Dynamic Links */
.dynamic-link {
  color: var(--brand-color);
}
.dynamic-link:hover {
  color: var(--brand-hover);
  text-decoration: underline;
}

/* Dynamic Submit Button */
.dynamic-btn {
  background-color: var(--brand-color);
  box-shadow: 0 10px 25px -5px color-mix(in srgb, var(--brand-color) 30%, transparent);
}
.dynamic-btn:hover {
  background-color: var(--brand-hover);
  box-shadow: 0 15px 30px -5px color-mix(in srgb, var(--brand-color) 40%, transparent);
}
</style>